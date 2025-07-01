from django.shortcuts import render
from rest_framework import viewsets, status, views
from rest_framework.permissions import IsAuthenticated
from rest_framework.decorators import action
from rest_framework.response import Response
from .models import *
from .serializers import * 

# Create your views here.

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


class DocumentoViewSet(viewsets.ModelViewSet): #--> Esta clase esta heredando de viewsets.ModelViewSet
    #API endpoint que te permite ver y editar los documentos
    serializer_class = DocumentoSerializer
    permission_classes = [IsAuthenticated]
    
    def get_queryset(self):
        """
        Este método filtra todos los documentos para devolver exclusivamente los documentos del cliente
        que los esta solicitando
        """
        user = self.request.user
        
        #Si el usuario es administrador
        if hasattr(user, 'administrador'):
            return Documento.objects.all().order_by('cliente__razon_social', 'tipo_documento__nombre')
        
        elif hasattr(user, 'cliente'):
            return Documento.objects.filter(cliente=user.cliente)
        
        #Si no es ninguno no devuelve nada
        return Documento.objects.none()
    
    @action(detail=True, methods=['post'], url_path="subir-cliente")
    def subir_archivo_cliente(self, request, pk=None):
        """
        Endpoint custom para que un cliente suba su archivo.
        Se accederá desde /api/documentos/<id>/subir-cliente/
        """
        documento = self.get_object()
        archivo = request.data.get('file')
        
        if not archivo:
            return Response({'Error': 'No se pudo subir el archivo, intente nuevamente.'}, status=status.HTTP_400_BAD_REQUEST)
        
        documento.archivo_cliente = archivo
        documento.save()
        
        #Esto devuelve los datos actualizados del documento
        serializers = self.get_serializer(documento)
        return Response(serializers.data)

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