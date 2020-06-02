# Grability Mobu Extension

## Instalación

Para realizar la instalación se deben agregar al archivo composer.json la dependencia y el repositorio

<pre>
"require": {
    ...
    "grability/module-mobu": "^1.0.0"
}
...
"repositories": [
    ...
    {
        "type": "vcs",
        "url": "git@github.com:grability-inc/saas-magento-extension.git"
    }
]
</pre>

Luego de modificar el archivo composer.json se deben correr los siguientes comandos

<pre>
composer update grability/module-mobu
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento cache:clean
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
</pre>

## Extension Webapi

### Anadir un nueva dirección con Postman

1. Debemos crear una autorización, esto nos genera un token_number:
<pre>
	curl -X POST "https://you-magento.host/index.php/rest/V1/integration/customer/token" \
	-H "Content-Type:application/xml" \
	-d '<login><username>customer@example.com</username><password>123123q</password></login>'
</pre>

2. Hacemos una petición con metodo **POST** a **https://you-magento.host/rest/V1/customers/addresses**. Seteando content-type: application/json y authoriaztion: bearer token_number en las cabeceras. Y en el **raw** del Body enviamos un **JSON** con la información de la dirección

<pre>
	{
		"address":{
			"firstname": "Jesus",
			"lastname": "Romero",
			"region":{
				"region_id": 667,
				"region": "Cundinamarca"
			},
			"country_id" : "CO",
			"city":"Bogota",
			"street": ["Calle Test 123"],
			"telephone":"123456678",
			"postcode":"33455",
			"default_shipping": true,
			"default_billing": true
		}
	}
</pre>

Como respuesta tendremos un codigo 200, con el addressId

## Configuración de webhooks

En el siguiente video se puede ver el proceso de configuración de un nuevo webhook:

[video de configuración](https://grability.atlassian.net/wiki/download/attachments/898433115/webhooks_configuration.mp4?api=v2)

![configuración](https://user-images.githubusercontent.com/63752877/80058652-77ca0580-84ef-11ea-91f8-fbf1a84df1f4.gif)

