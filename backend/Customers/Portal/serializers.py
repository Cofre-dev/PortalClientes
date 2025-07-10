from rest_framework import serializers
from .models import * 

class ClienteSerializer(serializers.ModelSerializer):
    class Meta:
        model = Cliente
        fields = ['razon_social', 'rut_empresa']

class TipoDocumentoSerializer(serializers.ModelSerializer):
    class Meta:
        model = TipoDocumento
        fields = ['nombre']
        

class ArchivoSubidoSerializer(serializers.ModelSerializer):
    archivo = serializers.FileField(use_url=True)
    class Meta:
        model = ArchivoSubido
        fields = ['id','archivo','subido_por','fecha_subida']

#DocumentoSerializer ahora incluye los datos del cliente
class CategoriaDocumentoSerializer(serializers.ModelSerializer):
        tipo_documento = TipoDocumentoSerializer(read_only=True)
        cliente = ClienteSerializer(read_only=True)
        
        # Incluye una lista de archivos en cada categoría
        archivos = ArchivoSubidoSerializer(many=True, read_only=True)

        class Meta:
            model = CategoriaDocumento
            fields = ['id', 'cliente', 'tipo_documento', 'archivos']