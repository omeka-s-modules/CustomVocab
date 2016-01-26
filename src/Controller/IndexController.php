<?php
namespace CustomVocab\Controller;

use CustomVocab\Form\CustomVocabForm;
use Omeka\Form\ConfirmForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function browseAction()
    {
        $response = $this->api()->search('custom_vocabs');
        $view = new ViewModel;
        $view->setVariable('vocabs', $response->getContent());
        $view->setVariable('confirmForm', new ConfirmForm(
            $this->getServiceLocator(), null, [
                'button_value' => $this->translate('Confirm Delete'),
            ]
        ));
        return $view;
    }

    public function showDetailsAction()
    {
        $response = $this->api()->read('custom_vocabs', $this->params('id'));
        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('vocab', $response->getContent());
        return $view;
    }

    public function addAction()
    {
        $action = $this->params('action');
        $form = new CustomVocabForm($this->getServiceLocator());

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());
            if ($form->isValid()) {
                $formData = $form->getData();
                $response = $this->api()->create('custom_vocabs', $formData);
                if ($response->isError()) {
                    $form->setMessages($response->getErrors());
                } else {
                    $this->messenger()->addSuccess('Custom vocab created.');
                    return $this->redirect()->toRoute('admin/custom-vocab');
                }
            } else {
                $this->messenger()->addError('There was an error during validation');
            }
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        return $view;
    }

    public function editAction()
    {
        $action = $this->params('action');
        $form = new CustomVocabForm($this->getServiceLocator());
        $response = $this->api()->read('custom_vocabs', $this->params('id'));
        $vocab = $response->getContent();
        $form->setData($vocab->jsonSerialize());

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());
            if ($form->isValid()) {
                $formData = $form->getData();
                $response = $this->api()->update('custom_vocabs', $vocab->id(), $formData);
                if ($response->isError()) {
                    $form->setMessages($response->getErrors());
                } else {
                    $this->messenger()->addSuccess('Custom vocab updated.');
                    return $this->redirect()->toRoute('admin/custom-vocab');
                }
            } else {
                $this->messenger()->addError('There was an error during validation');
            }
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        $view->setVariable('vocab', $vocab);
        return $view;
    }

    public function deleteAction()
    {
        if ($this->getRequest()->isPost()) {
            $form = new ConfirmForm($this->getServiceLocator());
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $response = $this->api()->delete('custom_vocabs', $this->params('id'));
                if ($response->isError()) {
                    $this->messenger()->addError('Vocab could not be deleted');
                } else {
                    $this->messenger()->addSuccess('Vocab successfully deleted');
                }
            } else {
                $this->messenger()->addError('Vocab could not be deleted');
            }
        }
        return $this->redirect()->toRoute('admin/custom-vocab');
    }
}
