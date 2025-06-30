from rest_framework import serializers
from .models import * 

class TipoDocumentoSerializer(serializers.ModelSerializer):
    class Meta:
        model = TipoDocumento
        fields = ['nombre', 'codigo']
        
class DocumentoSerializer(serializers.ModelSerializer):
    #En este campo se uso el serializer anterior para mostrar el nombre del documento + el ID
    tipo_documento = TipoDocumentoSerializer(read_only=True)
    
    #En estas lineas estamos haciendo que la URL del archivo sea completa para ser usada en el front
    archivo_consultora = serializers.FileFields(use_url=True, read_only=True)
    archivo_cliente = serializers.FileFields(use_url=True, read_only=True)
    
    class Meta:
        model = Documento
        fields = ['id', 'tipo_documento', 'archivo_consultora', 'archivo_cliente', 'fecha_actualizacion']