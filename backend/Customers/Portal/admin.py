from django.contrib import admin
from .models import * 

# Register your models here.
admin.site.register(Administrador)
admin.site.register(Cliente)
admin.site.register(TipoDocumento)
admin.site.register(CategoriaDocumento)
