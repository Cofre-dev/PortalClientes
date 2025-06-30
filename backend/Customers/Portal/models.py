from django.db import models
from django.contrib.auth.models import User

# Create your models here.

#Esta clase esta encargada de almacenar los datos de la empresa, como conectarse con el sistema de usuarios
class Cliente(models.Model):
    user = models.OneToOneField(User, on_delete=models.CASCADE) #Este campo será completado por el rut de la empresa
    razon_social = models.CharField(max_length=255)
    rut_empresa = models.CharField(max_length=12, unique=True, help_text="RUT sin puntos ni guion. ")
    
    def __str__(self):
        return self.razon_social

#Esta clase permitirá crear categorias de documentos desde el panel de administración de Django
class TipoDocumento(models.Model):
    nombre = models.CharField(max_length=150, unique=True)
    codigo = models.CharField(max_length=150, unique=True)
    
    def __str__(self):
        return self.nombre

#Esta clase se encarga de conectar al cliente con su respectivo documento
class Documento(models.Model):
    cliente = models.ForeignKey(Cliente, on_delete=models.CASCADE, related_name="Documentos")
    tipo_documento = models.ForeignKey(TipoDocumento, on_delete=models.PROTECT)
    
    #El campo upload_to --> organiza los archivos en carpetas por año y mes automáticamente.
    # Campo donde la empresa sube el archivo
    archivo_consultora = models.FileField(upload_to='documentos/consultora/%Y/%m/', null=True, blank=True)
    
    # Campo para el archivo que el cliente sube para la consultora
    archivo_cliente = models.FileField(upload_to='documentos/cliente/%Y/%m/', null=True, blank=True)
    
    fecha_actualizacion = models.DateTimeField(auto_now=True)
    
    class Meta:
        unique_together = ("cliente", "tipo_documento")
        
    def __str__(self):
        return f"{self.tipo_documento.nombre} - {self.cliente.razon_social}"