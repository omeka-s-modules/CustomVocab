<?php
namespace CustomVocab;

use Composer\Semver\Comparator;
use Omeka\Module\AbstractModule;
use Omeka\Permissions\Assertion\OwnsEntityAssertion;
use Zend\EventManager\Event;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $acl->allow(
            null,
            \CustomVocab\Controller\IndexController::class,
            ['browse', 'show-details']
        );
        $acl->allow(
            null,
            \CustomVocab\Api\Adapter\CustomVocabAdapter::class,
            ['search', 'read']
        );
        $acl->allow(
            null,
            \CustomVocab\Entity\CustomVocab::class,
            ['read']
        );
        $acl->allow(
            'editor',
            \CustomVocab\Controller\IndexController::class,
            ['add', 'edit', 'delete']
        );
        $acl->allow(
            'editor',
            \CustomVocab\Api\Adapter\CustomVocabAdapter::class,
            ['create', 'update', 'delete']
        );
        $acl->allow(
            'editor',
            \CustomVocab\Entity\CustomVocab::class,
            'create'
        );
        $acl->allow(
            'editor',
            \CustomVocab\Entity\CustomVocab::class,
            ['update', 'delete'],
            new OwnsEntityAssertion
        );
    }

    public function install(ServiceLocatorInterface $serviceLocator)
    {
        $conn = $serviceLocator->get('Omeka\Connection');
        $conn->exec('CREATE TABLE custom_vocab (id INT AUTO_INCREMENT NOT NULL, item_set_id INT DEFAULT NULL, owner_id INT DEFAULT NULL, `label` VARCHAR(190) NOT NULL, lang VARCHAR(190) DEFAULT NULL, terms LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_8533D2A5EA750E8 (`label`), INDEX IDX_8533D2A5960278D7 (item_set_id), INDEX IDX_8533D2A57E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;');
        $conn->exec('ALTER TABLE custom_vocab ADD CONSTRAINT FK_8533D2A5960278D7 FOREIGN KEY (item_set_id) REFERENCES item_set (id) ON DELETE SET NULL;');
        $conn->exec('ALTER TABLE custom_vocab ADD CONSTRAINT FK_8533D2A57E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) ON DELETE SET NULL;');
    }

    public function uninstall(ServiceLocatorInterface $serviceLocator)
    {
        $conn = $serviceLocator->get('Omeka\Connection');
        $conn->exec('SET FOREIGN_KEY_CHECKS=0;');
        $conn->exec('DROP TABLE custom_vocab');
        $conn->exec('SET FOREIGN_KEY_CHECKS=1;');
        // Set all types to a default state.
        $conn->exec('UPDATE value SET type = "literal" WHERE type REGEXP "^customvocab:[0-9]+$" AND value IS NOT NULL');
        $conn->exec('UPDATE value SET type = "resource:item" WHERE type REGEXP "^customvocab:[0-9]+$" AND value_resource_id IS NOT NULL');
        $conn->exec('UPDATE resource_template_property SET data_type = NULL WHERE data_type REGEXP "^customvocab:[0-9]+$"');
    }

    public function upgrade($oldVersion, $newVersion, ServiceLocatorInterface $services)
    {
        $conn = $services->get('Omeka\Connection');
        if (Comparator::lessThan($oldVersion, '1.2.0')) {
            // Add the item set field
            $conn->exec('ALTER TABLE custom_vocab ADD item_set_id int(11) DEFAULT NULL');
            $conn->exec('ALTER TABLE custom_vocab ADD CONSTRAINT FK_8533D2A5960278D7 FOREIGN KEY (item_set_id) REFERENCES item_set (id) ON DELETE SET NULL;');
            // Make `terms` DEFAULT NULL
            $conn->exec('ALTER TABLE `custom_vocab` CHANGE `terms` `terms` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
        }
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach(
            'Omeka\DataType\Manager',
            'service.registered_names',
            [$this, 'addVocabularyServices']
        );
        $sharedEventManager->attach(
            \CustomVocab\Entity\CustomVocab::class,
            'entity.remove.pre',
            [$this, 'setVocabTypeToDefaultState']
        );
        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Item',
            'view.add.after',
            [$this, 'prepareResourceForm']
        );
        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Item',
            'view.edit.after',
            [$this, 'prepareResourceForm']
        );
        $sharedEventManager->attach(
            'Omeka\Controller\Admin\ItemSet',
            'view.add.after',
            [$this, 'prepareResourceForm']
        );
        $sharedEventManager->attach(
            'Omeka\Controller\Admin\ItemSet',
            'view.edit.after',
            [$this, 'prepareResourceForm']
        );
        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Media',
            'view.add.after',
            [$this, 'prepareResourceForm']
        );
        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Media',
            'view.edit.after',
            [$this, 'prepareResourceForm']
        );
    }

    public function addVocabularyServices(Event $event)
    {
        $vocabs = $this->getServiceLocator()->get('Omeka\ApiManager')
            ->search('custom_vocabs')->getContent();
        if (!$vocabs) {
            return;
        }
        $names = $event->getParam('registered_names');
        foreach ($vocabs as $vocab) {
            $names[] = 'customvocab:' . $vocab->id();
        }
        $event->setParam('registered_names', $names);
    }

    public function setVocabTypeToDefaultState(Event $event)
    {
        $vocab = $event->getTarget();
        $vocabName = 'customvocab:' . $vocab->getId();
        $conn = $this->getServiceLocator()->get('Omeka\Connection');

        $stmt = $conn->prepare('UPDATE value SET type = "literal" WHERE type = ?');
        $stmt->bindValue(1, $vocabName);
        $stmt->execute();

        $stmt = $conn->prepare('UPDATE resource_template_property SET data_type = NULL WHERE data_type = ?');
        $stmt->bindValue(1, $vocabName);
        $stmt->execute();
    }

    /**
     * Prepare resource forms for custom vocab.
     *
     * @param Event $event
     */
    public function prepareResourceForm(Event $event)
    {
        $view = $event->getTarget();
        $view->headScript()->appendFile($view->assetUrl('js/custom-vocab.js', 'CustomVocab'));
    }
}
