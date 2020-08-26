<?php

namespace Grability\Mobu\Setup;

use Dotenv\Dotenv;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Psr\Log\LoggerInterface;

class InstallData implements InstallDataInterface
{
    protected $configurator = null;
    protected $configWriter;
    protected $dir;
    protected $eavSetupFactory;
    protected $logger;

    public function __construct(
        DirectoryList $dir,
        EavSetupFactory $eavSetupFactory,
        LoggerInterface $logger,
        WriterInterface $configWriter
    ) {
        $this->configWriter = $configWriter;
        $this->dir = $dir;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->logger = $logger;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->getConfigurator();

        $this->smtpLoader();
        $this->mercadopagoLoader();
        $this->webhooksLoader($setup);
        $this->widthAndHeigthLoader($setup);
        $this->isWhimLoader($setup);
        $this->isRecommendedLoader($setup);
        $this->logoLoader();

        $setup->endSetup();
    }

    public function getConfigurator()
    {
        if ($this->configurator === null) {
            try {
                $dotenv = new Dotenv($this->dir->getRoot());
                $dotenv->load();
                $this->configurator = $dotenv;
            } catch (\Exception $e) {
                $this->logger->error('Magento_Mobu -> environment file does not exist in project root');
            }
        }

        return $this->configurator;
    }

    /**
     * @param $path
     * @param $value
     */
    public function setData($path, $value)
    {
        $this->configWriter->save($path, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
    }

    public function smtpLoader()
    {
        $this->setData('trans_email/ident_general/email', 'owner@' . getenv('SMTP_OUTGOING_EMAIL_DOMAIN'));
        $this->setData('trans_email/ident_sales/email', 'sales@' . getenv('SMTP_OUTGOING_EMAIL_DOMAIN'));
        $this->setData('trans_email/ident_support/email', 'support@' . getenv('SMTP_OUTGOING_EMAIL_DOMAIN'));
        $this->setData('trans_email/ident_custom1/email', 'custom1@' . getenv('SMTP_OUTGOING_EMAIL_DOMAIN'));
        $this->setData('trans_email/ident_custom2/email', 'custom2@' . getenv('SMTP_OUTGOING_EMAIL_DOMAIN'));

        $this->setData('smtp/general/enabled', getenv('SMTP_GENERAL_ENABLED'));
        $this->setData('smtp/general/log_email', getenv('SMTP_GENERAL_LOG_EMAIL'));
        $this->setData('smtp/configuration_option/host', getenv('SMTP_CONFIGURATION_OPTION_HOST'));
        $this->setData('smtp/configuration_option/port', getenv('SMTP_CONFIGURATION_OPTION_PORT'));
        $this->setData('smtp/configuration_option/protocol', getenv('SMTP_CONFIGURATION_OPTION_PROTOCOL'));
        $this->setData('smtp/configuration_option/authentication', getenv('SMTP_CONFIGURATION_OPTION_AUTHENTICATION'));
        $this->setData('smtp/configuration_option/username', getenv('SMTP_CONFIGURATION_OPTION_USERNAME'));
        $this->setData('smtp/configuration_option/password', getenv('SMTP_CONFIGURATION_OPTION_PASSWORD'));
        $this->setData('smtp/module/product_key', getenv('SMTP_MODULE_PRODUCT_KEY'));

        $this->setData('free/module/email', getenv('SMTP_ACTIVATION_EMAIL'));
        $this->setData('free/module/name', getenv('SMTP_ACTIVATION_EMAIL'));
        $this->setData('free/module/create', '1');
        $this->setData('free/module/subscribe', '0');
        $this->setData('smtp/module/active', '1');
        $this->setData('smtp/module/email', getenv('SMTP_ACTIVATION_EMAIL'));
        $this->setData('smtp/module/name', getenv('SMTP_ACTIVATION_EMAIL'));
        $this->setData('smtp/module/create', '1');
        $this->setData('smtp/module/subscribe', '0');
    }

    public function mercadopagoLoader()
    {
        $this->setData('payment/mercadopago/public_key', getenv('PAYMENT_MERCADOPAGO_PUBLIC_KEY'));
        $this->setData('payment/mercadopago/access_token', getenv('PAYMENT_MERCADOPAGO_ACCESS_TOKEN'));
        $this->setData('payment/mercadopago_custom/active', '1');
        $this->setData('payment/mercadopago_basic/active', '0');
    }

    public function webhooksLoader(ModuleDataSetupInterface $setup)
    {
        $baseUrl = getenv('WEBHOOKS_BASE_PAYLOAD_URL');
        $appId= getenv('WEBHOOKS_APP_ID');

        $baseHook = [
            'status'        => '1',
            'store_ids'     => '0',
            'priority'      => '0',
            'headers'       => '{"_1592877368095_95":{"name":"app_id","value":"'. $appId .'"}}',
            'content_type'  => 'application/json',
        ];

        $hooks = [
            [
                'name'          => 'On Product Create',
                'hook_type'     => 'new_product',
                'payload_url'   => "{$baseUrl}/shop-integrations/product",
                'method'        => 'POST',
                'body'          => '{"sku": "{{ item.sku }}"}',
            ],
            [
                'name'          => 'On Product Update',
                'hook_type'     => 'update_product',
                'payload_url'   => "{$baseUrl}/shop-integrations/product",
                'method'        => 'PUT',
                'body' => '{"sku": "{{ item.sku }}"}',
            ],
            [
                'name'          => 'On Product Delete',
                'hook_type'     => 'delete_product',
                'payload_url'   => "{$baseUrl}/shop-integrations/product",
                'method'        => 'DELETE',
                'body'          => '{"sku": "{{ item.sku }}"}',
            ],
            [
                'name'          => 'On Category Create',
                'hook_type'     => 'new_category',
                'payload_url'   => "{$baseUrl}/shop-integrations/category",
                'method'        => 'POST',
                'body'          => '{"categoryId": "{{ item.entity_id }}"}',
            ],
            [
                'name'          => 'On Category Update',
                'hook_type'     => 'update_category',
                'payload_url'   => "{$baseUrl}/shop-integrations/category",
                'method'        => 'PUT',
                'body'          => '{"categoryId": "{{ item.entity_id }}"}',
            ],
            [
                'name'          => 'On Category Delete',
                'hook_type'     => 'delete_category',
                'payload_url'   => "{$baseUrl}/shop-integrations/category",
                'method'        => 'DELETE',
                'body'          => '{"categoryId": "{{ item.entity_id }}"}',
            ],
            [
                'name'          => 'On Import Products',
                'hook_type'     => 'import_products',
                'payload_url'   => "{$baseUrl}/shop-integrations/product/bulk",
                'method'        => 'POST',
                'body'          => '{{ item.bunch }}',
            ],
            [
                'name'          => 'On Delete Products',
                'hook_type'     => 'delete_products',
                'payload_url'   => "{$baseUrl}/shop-integrations/product/bulk",
                'method'        => 'DELETE',
                'body'          => '{{ item.bunch }}',
            ],
        ];

        foreach ($hooks as $hook) {
            $setup->getConnection()->insert(
                $setup->getTable('mageplaza_webhook_hook'),
                array_merge($baseHook, $hook)
            );
        }
    }

    public function widthAndHeigthLoader(ModuleDataSetupInterface $setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'width',
            [
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => 'Width',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => '',
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'height',
            [
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => 'Height',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => ''
            ]
        );
    }

    public function logoLoader()
    {
        try {
            $url = getenv('LOGO_URL');

            $contents = file_get_contents($url);

            $rootDir = $this->dir->getRoot();
            $name = 'custom_logo.' . getenv('LOGO_EXTENSION');

            $imgHeader = "{$rootDir}/pub/media/logo/default/{$name}";
            $imgEmails = "{$rootDir}/pub/media/email/logo/default/{$name}";

            file_put_contents($imgHeader, $contents);
            file_put_contents($imgEmails, $contents);

            $this->setData('design/header/logo_src', "default/{$name}");
            $this->setData('design/email/logo', "default/{$name}");
        } catch (\Exception $e) {
            $this->logger->error('Magento_Mobu -> invalid logo url');
        }
    }

    protected function isWhimLoader(ModuleDataSetupInterface $setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'isWhim',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Is Whim',
                'input' => 'boolean',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => '',
            ]
        );
    }

    protected function isRecommendedLoader(ModuleDataSetupInterface $setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'isRecommended',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Is Recommended',
                'input' => 'boolean',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => '',
            ]
        );
    }
}