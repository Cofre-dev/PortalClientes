from django.shortcuts import render
from rest_framework import viewsets, status, views, generics
from rest_framework.permissions import IsAuthenticated
from rest_framework.decorators import action, api_view, permission_classes
from rest_framework.response import Response
from django.db.models import Max, Count
from django.db import transaction
from .models import *
from .serializers import *
# Archivos por mes (últimos 6 meses)
from django.utils import timezone
from datetime import datetime, timedelta

class ProfileView(views.APIView):
    permission_classes = [IsAuthenticated] 

    def get(self, request, *args, **kwargs):
        
        if hasattr(request.user, 'administrador'):
            profile = request.user.administrador    
            data = {
                'username': request.user.username,
                'full_name':request.user.get_full_name(),
                'role_type':'administrador',
                'role_detail': profile.rol,
            }
            return Response(data)
        
        elif hasattr(request.user, 'cliente'):
            profile = request.user.cliente
            data = {
                'username': request.user.username,
                'role_type':'cliente',
                'company_name': profile.razon_social,
            }
            return Response(data)
        
        return Response({'Error': 'Perfil no encontrado'}, status=404)


class CategoriaDocumentoViewSet(viewsets.ModelViewSet): 
    #API endpoint que permite ver y editar los documentos
    serializer_class = CategoriaDocumentoSerializer
    permission_classes = [IsAuthenticated]
    
    def get_queryset(self):
        user = self.request.user
        
        if hasattr(user, 'administrador'):
            return CategoriaDocumento.objects.all().order_by('cliente__razon_social', 'tipo_documento__nombre')
        
        elif hasattr(user, 'cliente'):
            return CategoriaDocumento.objects.filter(cliente=user.cliente)
        
        return CategoriaDocumento.objects.none()
    
    #EndPoint para subir archivos
    @action(detail=True, methods=['post'], url_path='upload-file')
    def upload_file(self, request, pk=None):
        categoria = self.get_object()
        archivo = request.data.get('file')
        subido_por = 'cliente' if hasattr(request.user, 'cliente') else 'Ara y bustamante'

        if not archivo:
            return Response({'error': 'No se envió ningún archivo.'}, status=status.HTTP_400_BAD_REQUEST)
        
        next_correlativo = (categoria.archivos.aggregate(Max('correlativo'))['correlativo__max'] or 0) + 1

        ArchivoSubido.objects.create(
            categoria=categoria,
            archivo=archivo,
            subido_por=subido_por,
            correlativo = next_correlativo,
        )
        return Response({'status': 'archivo subido'}, status=status.HTTP_201_CREATED)

    @action(detail=True, methods=['post'], url_path='subir-consultora')
    def subir_archivo_consultora(self, request, pk=None):
        
        documento = self.get_object()
        archivo = request.data.get('file')
        if not archivo:
            return Response({'error:' 'No se logro subir ningun archivo'}, status=status.HTTP_400_BAD_REQUEST)

        documento.archivo_consultora = archivo
        documento.save()
        
        serializers = self.get_serializer(documento)
        return Response(serializers.data)
    
    
class ArchivoSubidoDeleteView(generics.DestroyAPIView):
        queryset = ArchivoSubido.objects.all()
        permission_classes = [IsAuthenticated]

# ===== NUEVOS ENDPOINTS PARA ADMINISTRADORES =====

def is_admin(user):
    """Helper function to check if user is admin"""
    return hasattr(user, 'administrador')

class ClienteViewSet(viewsets.ModelViewSet):
    """ViewSet para gestionar clientes - Solo administradores"""
    queryset = Cliente.objects.all()
    permission_classes = [IsAuthenticated]

    def get_serializer_class(self):
        if self.action == 'create':
            return CreateClienteSerializer
        return ClienteSerializer

    def get_queryset(self):
        if not is_admin(self.request.user):
            return Cliente.objects.none()
        return Cliente.objects.all().order_by('razon_social')

    def perform_create(self, serializer):
        if not is_admin(self.request.user):
            raise PermissionError("Solo administradores pueden crear clientes")
        serializer.save()

class AdministradorViewSet(viewsets.ModelViewSet):
    """ViewSet para gestionar administradores - Solo administradores"""
    queryset = Administrador.objects.all()
    permission_classes = [IsAuthenticated]

    def get_serializer_class(self):
        if self.action == 'create':
            return CreateAdministradorSerializer
        return AdministradorSerializer

    def get_queryset(self):
        if not is_admin(self.request.user):
            return Administrador.objects.none()
        return Administrador.objects.all().order_by('user__username')

    def perform_create(self, serializer):
        if not is_admin(self.request.user):
            raise PermissionError("Solo administradores pueden crear administradores")
        serializer.save()

class TipoDocumentoViewSet(viewsets.ModelViewSet):
    """ViewSet para gestionar tipos de documento - Solo administradores"""
    queryset = TipoDocumento.objects.all()
    permission_classes = [IsAuthenticated]

    def get_serializer_class(self):
        if self.action == 'create':
            return CreateTipoDocumentoSerializer
        return TipoDocumentoSerializer

    def get_queryset(self):
        if not is_admin(self.request.user):
            return TipoDocumento.objects.none()
        return TipoDocumento.objects.all().order_by('nombre')

    def perform_create(self, serializer):
        if not is_admin(self.request.user):
            raise PermissionError("Solo administradores pueden crear tipos de documento")
        serializer.save()

@api_view(['POST'])
@permission_classes([IsAuthenticated])
def asignar_documentos(request):
    """Endpoint para asignar documentos a clientes"""
    if not is_admin(request.user):
        return Response(
            {'error': 'Solo administradores pueden asignar documentos'},
            status=status.HTTP_403_FORBIDDEN
        )

    serializer = AsignarDocumentoSerializer(data=request.data)
    if serializer.is_valid():
        cliente_id = serializer.validated_data['cliente_id']
        tipo_documento_ids = serializer.validated_data['tipo_documento_ids']

        try:
            with transaction.atomic():
                cliente = Cliente.objects.get(id=cliente_id)
                created_count = 0
                existing_count = 0

                for tipo_doc_id in tipo_documento_ids:
                    tipo_documento = TipoDocumento.objects.get(id=tipo_doc_id)
                    categoria, created = CategoriaDocumento.objects.get_or_create(
                        cliente=cliente,
                        tipo_documento=tipo_documento
                    )
                    if created:
                        created_count += 1
                    else:
                        existing_count += 1

                return Response({
                    'message': 'Documentos asignados exitosamente',
                    'cliente': cliente.razon_social,
                    'nuevas_asignaciones': created_count,
                    'ya_existian': existing_count
                }, status=status.HTTP_201_CREATED)

        except Exception as e:
            return Response(
                {'error': f'Error al asignar documentos: {str(e)}'},
                status=status.HTTP_400_BAD_REQUEST
            )

    return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)

@api_view(['GET'])
@permission_classes([IsAuthenticated])
def admin_stats(request):
    """Endpoint para obtener estadísticas del panel de administrador"""
    if not is_admin(request.user):
        return Response(
            {'error': 'Solo administradores pueden ver estadísticas'},
            status=status.HTTP_403_FORBIDDEN
        )

    # Calcular estadísticas
    total_clientes = Cliente.objects.count()
    total_administradores = Administrador.objects.count()
    total_tipos_documento = TipoDocumento.objects.count()
    total_categorias = CategoriaDocumento.objects.count()
    total_archivos = ArchivoSubido.objects.count()

    # Estadísticas por cliente
    clientes_con_documentos = Cliente.objects.annotate(
        documentos_count=Count('Documentos__archivos')
    ).filter(documentos_count__gt=0).count()

    clientes_sin_documentos = total_clientes - clientes_con_documentos



    now = timezone.now()
    six_months_ago = now - timedelta(days=180)

    archivos_recientes = ArchivoSubido.objects.filter(
        fecha_subida__gte=six_months_ago
    ).count()

    return Response({
        'total_clientes': total_clientes,
        'total_administradores': total_administradores,
        'total_tipos_documento': total_tipos_documento,
        'total_categorias': total_categorias,
        'total_archivos': total_archivos,
        'clientes_con_documentos': clientes_con_documentos,
        'clientes_sin_documentos': clientes_sin_documentos,
        'archivos_ultimos_6_meses': archivos_recientes,
    }, status=status.HTTP_200_OK)

@api_view(['GET'])
@permission_classes([IsAuthenticated])
def get_all_documents(request):
    #Endpoint para obtener todos los documentos del sistema (solo admins)
    if not is_admin(request.user):
        return Response(
            {'error': 'Solo administradores pueden ver todos los documentos'},
            status=status.HTTP_403_FORBIDDEN
        )

    # Obtener todos los archivos con información de cliente y categoría
    archivos = ArchivoSubido.objects.select_related(
        'categoria__cliente',
        'categoria__tipo_documento'
    ).order_by('-fecha_subida')

    # Formatear datos para el frontend
    documentos_data = []
    for archivo in archivos:
        documentos_data.append({
            'id': archivo.id,
            'nombre_archivo': archivo.archivo.name.split('/')[-1],
            'url_archivo': archivo.archivo.url if archivo.archivo else None,
            'cliente': {
                'id': archivo.categoria.cliente.id,
                'razon_social': archivo.categoria.cliente.razon_social,
                'rut_empresa': archivo.categoria.cliente.rut_empresa,
            },
            'tipo_documento': {
                'id': archivo.categoria.tipo_documento.id,
                'nombre': archivo.categoria.tipo_documento.nombre,
            },
            'categoria_id': archivo.categoria.id,
            'correlativo': archivo.correlativo,
            'subido_por': archivo.subido_por,
            'fecha_subida': archivo.fecha_subida,
        })

    return Response(documentos_data, status=status.HTTP_200_OK)

@api_view(['POST'])
@permission_classes([IsAuthenticated])
def upload_document_admin(request):
    #Endpoint para que admins suban documentos a clientes
    if not is_admin(request.user):
        return Response(
            {'error': 'Solo administradores pueden subir documentos'},
            status=status.HTTP_403_FORBIDDEN
        )

    categoria_id = request.data.get('categoria_id')
    archivo = request.data.get('file')

    if not categoria_id or not archivo:
        return Response(
            {'error': 'Se requiere categoria_id y archivo'},
            status=status.HTTP_400_BAD_REQUEST
        )

    try:
        categoria = CategoriaDocumento.objects.get(id=categoria_id)
        next_correlativo = (categoria.archivos.aggregate(Max('correlativo'))['correlativo__max'] or 0) + 1

        nuevo_archivo = ArchivoSubido.objects.create(
            categoria=categoria,
            archivo=archivo,
            subido_por='Ara y Bustamante',
            correlativo=next_correlativo,
        )

        return Response({
            'message': 'Archivo subido exitosamente',
            'archivo_id': nuevo_archivo.id,
            'cliente': categoria.cliente.razon_social,
            'tipo_documento': categoria.tipo_documento.nombre,
            'correlativo': nuevo_archivo.correlativo,
        }, status=status.HTTP_201_CREATED)

    except CategoriaDocumento.DoesNotExist:
        return Response(
            {'error': 'Categoría de documento no encontrada'},
            status=status.HTTP_404_NOT_FOUND
        )
    except Exception as e:
        return Response(
            {'error': f'Error al subir archivo: {str(e)}'},
            status=status.HTTP_400_BAD_REQUEST
        )