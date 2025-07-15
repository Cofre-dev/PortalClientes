from django.shortcuts import render
from rest_framework import viewsets, status, views, generics
from rest_framework.permissions import IsAuthenticated
from rest_framework.decorators import action
from rest_framework.response import Response
from .models import *
from .serializers import * 

class ProfileView(views.APIView):
    permission_classes = [IsAuthenticated] 

    def get(self, request, *args, **kwargs):
        
        if hasattr(request.user, 'administrador'):
            profile = request.user.administrador    
            #Diccionario con datos de los empleados
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
        subido_por = 'cliente' if hasattr(request.user, 'cliente') else 'consultora'

        if not archivo:
            return Response({'error': 'No se envió ningún archivo.'}, status=status.HTTP_400_BAD_REQUEST)

        ArchivoSubido.objects.create(
            categoria=categoria,
            archivo=archivo,
            subido_por=subido_por
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