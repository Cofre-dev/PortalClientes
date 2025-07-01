from rest_framework import serializers
from .models import * 

class ClienteSerializer(serializers.ModelSerializer):
    class Meta:
        model = Cliente
        fields = ['razon_social', 'rut_empresa']

class TipoDocumentoSerializer(serializers.ModelSerializer):
    class Meta:
        model = TipoDocumento
        fields = ['nombre', 'codigo']
        
       
#DocumentoSerializer ahora incluye los datos del cliente
class DocumentoSerializer(serializers.ModelSerializer):
    tipo_documento = TipoDocumentoSerializer(read_only=True)
    
    #Esta línea adjunta la información del cliente a cada documento.
    cliente = ClienteSerializer(read_only=True)
    
    archivo_consultora = serializers.FileField(use_url=True, read_only=True)
    archivo_cliente = serializers.FileField(use_url=True, read_only=True)

    class Meta:
        model = Documento
        fields = ['id', 'cliente', 'tipo_documento', 'archivo_consultora', 'archivo_cliente', 'fecha_actualizacion']