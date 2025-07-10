# Portal/management/commands/init_data.py
from django.core.management.base import BaseCommand
from django.contrib.auth import get_user_model
from .models import * 
import os

class Command(BaseCommand):
    help = 'Crea un superusuario si no existe y un usuario de prueba.'

    def handle(self, *args, **kwargs):
        User = get_user_model()
        if not User.objects.filter(username='admin').exists():
            User.objects.create_superuser('mcofre', 'admin@example.com', 'Matias321')
            self.stdout.write(self.style.SUCCESS('Superusuario "admin" creado.'))
        else:
            self.stdout.write(self.style.WARNING('Superusuario "admin" ya existe.'))

        if not User.objects.filter(username='testclient').exists():
            from Portal.models import Cliente
            user_test = User.objects.create_user('testclient', 'test@client.com', 'passwordcliente123')
            Cliente.objects.create(user=user_test, razon_social='Cliente de Prueba S.A.', rut_empresa='12345678-9') # Ajusta estos datos
            self.stdout.write(self.style.SUCCESS('Usuario cliente de prueba "testclient" creado.'))
        else:
            self.stdout.write(self.style.WARNING('Usuario cliente de prueba "testclient" ya existe.'))

        self.stdout.write(self.style.SUCCESS('¡Datos de inicialización verificados/creados!'))