# Portal/management/commands/create_initial_users.py
from django.core.management.base import BaseCommand
from django.contrib.auth import get_user_model
from Portal.models import Cliente, Administrador # Asegúrate de importar tus modelos personalizados
import os

class Command(BaseCommand):
    help = 'Crea un superusuario y un usuario cliente de prueba si no existen.'

    def handle(self, *args, **kwargs):
        User = get_user_model()

        #Crear Superusuario
        admin_username = os.environ.get('DEFAULT_ADMIN_USERNAME', 'mcofre')
        admin_email = os.environ.get('DEFAULT_ADMIN_EMAIL', 'matias@gmail.com')
        admin_password = os.environ.get('DEFAULT_ADMIN_PASSWORD', 'Matias321')

        if not User.objects.filter(username=admin_username).exists():
            self.stdout.write(self.style.SUCCESS(f'Creando superusuario "{admin_username}"...'))
            superuser = User.objects.create_superuser(
                username=admin_username,
                email=admin_email,
                password=admin_password
            )
            Administrador.objects.create(user=superuser, rol='TI')
            self.stdout.write(self.style.SUCCESS(f'Superusuario "{admin_username}" creado exitosamente.'))
        else:
            self.stdout.write(self.style.WARNING(f'Superusuario "{admin_username}" ya existe. Saltando creación.'))

        client_username = os.environ.get('DEFAULT_CLIENT_USERNAME', 'cara')
        client_email = os.environ.get('DEFAULT_CLIENT_EMAIL', 'carlosara@gmail.com')
        client_password = os.environ.get('DEFAULT_CLIENT_PASSWORD', 'Carlos321')

        if not User.objects.filter(username=client_username).exists():
            self.stdout.write(self.style.SUCCESS(f'Creando usuario cliente "{client_username}"...'))
            user_client = User.objects.create_user(
                username=client_username,
                email=client_email,
                password=client_password
            )
            Cliente.objects.create(user=user_client, razon_social='Cofré Solutions', rut_empresa='21546132-7')
            self.stdout.write(self.style.SUCCESS(f'Usuario cliente "{client_username}" creado exitosamente.'))
        else:
            self.stdout.write(self.style.WARNING(f'Usuario cliente "{client_username}" ya existe. Saltando creación.'))

        self.stdout.write(self.style.SUCCESS('¡Verificación/Creación de usuarios iniciales completada!'))