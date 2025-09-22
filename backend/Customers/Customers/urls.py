# Actualizar tu customers/urls.py con estos endpoints adicionales

from django.contrib import admin
from django.urls import path, include
from rest_framework import routers
from Portal import views as portal_views
from rest_framework_simplejwt.views import (
    TokenObtainPairView,
    TokenRefreshView,
)
from django.conf import settings
from django.conf.urls.static import static 

#El router crea automaticamente las URLs para nuestro ViewSet
router = routers.DefaultRouter()
router.register(r'categorias', portal_views.CategoriaDocumentoViewSet, basename='categoria')
router.register(r'clientes', portal_views.ClienteViewSet, basename='cliente')
router.register(r'administradores', portal_views.AdministradorViewSet, basename='administrador')
router.register(r'tipos-documento', portal_views.TipoDocumentoViewSet, basename='tipo-documento')

urlpatterns = [
    path('admin/', admin.site.urls),
    
    #---- Endpoints para autenticación JWT ----
    path('api/token/', TokenObtainPairView.as_view(), name='token_obtain_pair'),
    path('api/token/refresh/', TokenRefreshView.as_view(), name='token_refresh'),
    path('api/me/', portal_views.ProfileView.as_view(), name='user-profile'),
    path('api/archivos/<int:pk>/', portal_views.ArchivoSubidoDeleteView.as_view(), name='archivo-delete'),

    #---- Endpoints de administración ----
    path('api/asignar-documentos/', portal_views.asignar_documentos, name='asignar-documentos'),
    path('api/admin-stats/', portal_views.admin_stats, name='admin-stats'),
    path('api/admin-documents/', portal_views.get_all_documents, name='admin-documents'),
    path('api/admin-upload/', portal_views.upload_document_admin, name='admin-upload'),
    
    #---- Endpoints de la API original ----
    path('api/', include(router.urls)),
]

if settings.DEBUG:
    urlpatterns += static(settings.MEDIA_URL, document_root=settings.MEDIA_ROOT)