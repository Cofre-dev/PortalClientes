from django.shortcuts import render
from rest_framework import viewsets, status
from rest_framework.permissions import IsAuthenticated
from rest_framework.decorators import action
from rest_framework.response import Response
from .models import *
from .serializers import * 

# Create your views here.

class DocumentoViewSet(viewsets.ModelViewSet): #--> Esta clase esta heredando de viewsets.ModelViewSet
    #API endpoint que te permite ver y editar los documentos
    serializer_class = DocumentoSerializer
    permission_classes = [IsAuthenticated]
    
    def get_queryset(self):
        """
        Este método filtra todos los documentos para devolver exclusivamente los documentos del cliente
        que los esta solicitando
        """
        #Ojo con esta linea!!!
        return Documento.objects.filter(Cliente=self.request.user.cliente)
    
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
        serializers = self.get_serializers(documento)
        return Response(serializers.data)
