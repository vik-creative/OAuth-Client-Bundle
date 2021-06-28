Бандл клиента авторизации для Integration Hub
-
### Установка

1. Добавить в `composer.json` элемент `repositories` либо дополнить его:
    ```
    "repositories": [
        {
            "type": "git",
            "url": "https://git.crtweb.ru/creative-packages/oauth-bundle.git"
        }
    ]
    ```
2. Выполнить команду:
    ```
    composer require creative/auth-client-bundle
    ```

3. Добавить файл `config/packages/auth_client.yaml`:
    ```yaml
    auth_client:
      client:
         oauth_server_url: '%creative.oauth_service_url%'
         client_id: '%creative.client_id%'
         client_secret: '%creative.client_secret%'
         redirect_uri: '%creative.auth_redirect_uri%'

   jwt_parser:
      public_key_path: '%kernel.project_dir%/config/jwt/public.key'

   user_provider:
      class: App\Security\CreativeOauthUserProvider

   authenticator:
      class: App\Security\CreativeTokenAuthenticator
    ```

4. Добавить запись в файл `config/bundles.php`
    ```
   Creative\AuthClientBundle\AuthClientBundle::class => ['all' => true]
   ```

### Настройка 
1. в файл `.env.local` добавить 
````
CRT_CLIENT_ID=*client-id*
CRT_CLIENT_SECRET=*secret-key*
CRT_AUTH_REDIRECT_URI=*redirect-url*
CRT_AUTH_SERVICE_URL=*auth-service-url*
````
Значения заполняем согласно серверу авторизации.
Для работы сервиса необходимо реализовать `UserProvider` и `Authenticator`
