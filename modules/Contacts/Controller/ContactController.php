<?php

namespace Modules\Contacts\Controller;

use Modules\Contacts\Entity\Contact;
use Modules\Contacts\Form\ContactFormType;
use Modules\Contacts\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContactController extends AbstractController
{
    private const ERROR_FORM = 'Chyba ve formuláři';

    public function __construct(private readonly ContactRepository $contactRepository)
    {
    }

    /** Domovská stránka adresáře.
     *
     * @throws \Exception
     */
    #[Route('/', name: 'contacts_home')]
    public function list(Request $request): Response
    {
        $page = $request->get('page', 1);
        $form = $this->createForm(ContactFormType::class, new Contact());
        $form->handleRequest($request);

        /* Přidaní nového kontaktu. */
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ($data) {
                $ret = $this->contactRepository->isValidData($data, true);

                if (true === $ret) {
                    $this->contactRepository->save($data, true);
                    /* Vymazat hodnoty formuláře po úspěšném odeslání. */
                    unset($form);
                    $form = $this->createForm(ContactFormType::class, new Contact());
                } else {
                    $form->addError(new FormError('boolean' === gettype($ret) ? self::ERROR_FORM : $ret));
                }
            } else {
                $form->addError(new FormError(self::ERROR_FORM));
            }
        }

        return $this->render('@contacts/list.html.twig', [
            'form' => $form->createView(),
            'contacts' => $this->contactRepository->getList($page),
            'path_home' => $this->generateUrl('contacts_home', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }

    /** Route pro smazání kontaktu. */
    #[Route('/delete/{slug}', name: 'contact_delete')]
    public function deleteContact(string $slug): RedirectResponse
    {
        $contact = $this->contactRepository->findOneBySlug($slug);

        if (null === $contact) {
            throw $this->createNotFoundException('Kontakt neexistuje, nejde je odstranit.');
        }

        $this->contactRepository->remove($contact, true);

        return $this->redirectToRoute('contacts_home');
    }

    /** Route pro načtení poznámky. */
    #[Route('/note/{slug}', name: 'contact_note')]
    public function readNote(string $slug): JsonResponse
    {
        $contact = $this->contactRepository->findOneBySlug($slug);

        if (null === $contact) {
            throw $this->createNotFoundException('Kontakt neexistuje, nejde získat jeho poznámky.');
        }

        return new JsonResponse(['note' => $contact->getNote()]);
    }

    /** Stránka pro zobrazení a úpravu kontaktu. */
    #[Route('/edit/{slug}', name: 'contact_edit')]
    public function showContact(string $slug, Request $request): Response
    {
        $form = null;
        $template = '@contacts/edit.html.twig';
        $contact = $this->contactRepository->findOneBySlug($slug);

        if (empty($contact)) {
            $template = '@contacts/error.html.twig';
        } else {
            $form = $this->createForm(ContactFormType::class, $contact);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();

                if ($data) {
                    $ret = $this->contactRepository->isValidData($data);

                    if (true === $ret) {
                        $this->contactRepository->save($data, true);

                        return $this->redirectToRoute('contacts_home');
                    } else {
                        $form->addError(new FormError('boolean' === gettype($ret) ? self::ERROR_FORM : $ret));
                    }
                } else {
                    $form->addError(new FormError(self::ERROR_FORM));
                }
            }
        }

        return $this->render($template, [
            'path_home' => $this->generateUrl('contacts_home', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'slug' => $slug,
            'form' => $form?->createView(),
        ]);
    }
}
