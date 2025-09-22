from rest_framework import serializers
from .models import *
from django.contrib.auth.models import User
from django.contrib.auth.hashers import make_password

class ClienteSerializer(serializers.ModelSerializer):
    username = serializers.CharField(source='user.username', read_only=True)
    full_name = serializers.CharField(source='user.get_full_name', read_only=True)
    email = serializers.EmailField(source='user.email', read_only=True)

    class Meta:
        model = Cliente
        fields = ['id', 'razon_social', 'rut_empresa', 'username', 'full_name', 'email']

class TipoDocumentoSerializer(serializers.ModelSerializer):
    class Meta:
        model = TipoDocumento
        fields = ['id', 'nombre']

class AdministradorSerializer(serializers.ModelSerializer):
    username = serializers.CharField(source='user.username', read_only=True)
    full_name = serializers.CharField(source='user.get_full_name', read_only=True)
    email = serializers.EmailField(source='user.email', read_only=True)

    class Meta:
        model = Administrador
        fields = ['id', 'rol', 'username', 'full_name', 'email']

# Serializers para crear usuarios
class CreateClienteSerializer(serializers.ModelSerializer):
    username = serializers.CharField(write_only=True)
    password = serializers.CharField(write_only=True, min_length=8)
    email = serializers.EmailField(write_only=True, required=False)
    first_name = serializers.CharField(write_only=True, required=False)
    last_name = serializers.CharField(write_only=True, required=False)

    class Meta:
        model = Cliente
        fields = ['razon_social', 'rut_empresa', 'username', 'password', 'email', 'first_name', 'last_name']

    def create(self, validated_data):
        # Extraer datos del usuario
        user_data = {
            'username': validated_data.pop('username'),
            'password': make_password(validated_data.pop('password')),
            'email': validated_data.pop('email', ''),
            'first_name': validated_data.pop('first_name', ''),
            'last_name': validated_data.pop('last_name', ''),
        }

        # Crear usuario
        user = User.objects.create(**user_data)

        # Crear cliente
        cliente = Cliente.objects.create(user=user, **validated_data)
        return cliente

class CreateAdministradorSerializer(serializers.ModelSerializer):
    username = serializers.CharField(write_only=True)
    password = serializers.CharField(write_only=True, min_length=8)
    email = serializers.EmailField(write_only=True, required=False)
    first_name = serializers.CharField(write_only=True, required=False)
    last_name = serializers.CharField(write_only=True, required=False)

    class Meta:
        model = Administrador
        fields = ['rol', 'username', 'password', 'email', 'first_name', 'last_name']

    def create(self, validated_data):
        # Extraer datos del usuario
        user_data = {
            'username': validated_data.pop('username'),
            'password': make_password(validated_data.pop('password')),
            'email': validated_data.pop('email', ''),
            'first_name': validated_data.pop('first_name', ''),
            'last_name': validated_data.pop('last_name', ''),
        }

        # Crear usuario
        user = User.objects.create(**user_data)

        # Crear administrador
        administrador = Administrador.objects.create(user=user, **validated_data)
        return administrador

class CreateTipoDocumentoSerializer(serializers.ModelSerializer):
    class Meta:
        model = TipoDocumento
        fields = ['nombre']

class AsignarDocumentoSerializer(serializers.Serializer):
    cliente_id = serializers.IntegerField()
    tipo_documento_ids = serializers.ListField(
        child=serializers.IntegerField(),
        allow_empty=False
    )

    def validate_cliente_id(self, value):
        try:
            Cliente.objects.get(id=value)
        except Cliente.DoesNotExist:
            raise serializers.ValidationError("Cliente no encontrado")
        return value

    def validate_tipo_documento_ids(self, value):
        existing_ids = TipoDocumento.objects.filter(id__in=value).values_list('id', flat=True)
        if len(existing_ids) != len(value):
            raise serializers.ValidationError("Algunos tipos de documento no existen")
        return value


class ArchivoSubidoSerializer(serializers.ModelSerializer):
    archivo = serializers.FileField(use_url=True)
    class Meta:
        model = ArchivoSubido
        fields = ['id','correlativo','archivo','subido_por','fecha_subida']   


class CategoriaDocumentoSerializer(serializers.ModelSerializer):
        tipo_documento = TipoDocumentoSerializer(read_only=True)
        cliente = ClienteSerializer(read_only=True)
        
        archivos = ArchivoSubidoSerializer(many=True, read_only=True)

        class Meta:
            model = CategoriaDocumento
            fields = ['id', 'cliente', 'tipo_documento', 'archivos']           
            
         
