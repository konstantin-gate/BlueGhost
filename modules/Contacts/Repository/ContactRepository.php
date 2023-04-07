<?php

namespace Modules\Contacts\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Modules\Contacts\Entity\Contact;
use Modules\Utils\Paginator;

/**
 * @extends ServiceEntityRepository<Contact>
 *
 * @method Contact|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contact|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contact[]    findAll()
 * @method Contact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactRepository extends ServiceEntityRepository
{
    private const ERROR_EMAIL = 'Zadejte správný e-mail';
    private const ERROR_NOT_UNIQUE_EMAIL = 'Tento e-mail je již zaregistrován, zadejte prosím jiný.';
    private const ERROR_PHONE = 'Zadejte správné české telefonní číslo';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    public function save(Contact $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Contact $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Vyhledá Contact podle $slug.
     */
    public function findOneBySlug(string $slug): null|Contact
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * Zkontrolujte, zda jsou údaje odeslané do 'Contact' správné.
     * Pokud ne, vrátí chybovou textovou zprávu, jinak true.
     *
     * @param bool $uniqueEmail zda kontrolovat jedinečnost e-mailu, nebo ne
     */
    public function isValidData(Contact $contact, bool $uniqueEmail = false): bool|string
    {
        $pattern = '~^(\+420)? ?[1-9][0-9]{2} ?[0-9]{3} ?[0-9]{3}$~';
        $phone = $contact->getPhone();

        if (!empty($phone) && !preg_match($pattern, $phone)) {
            return self::ERROR_PHONE;
        }

        if (!filter_var($contact->getEmail(), FILTER_VALIDATE_EMAIL)) {
            return self::ERROR_EMAIL;
        }

        if ($uniqueEmail) {
            if ($this->findOneBy(['email' => $contact->getEmail()])) {
                return self::ERROR_NOT_UNIQUE_EMAIL;
            }
        }

        return true;
    }

    /**
     * Získat seznam kontaktů na stránku.
     *
     * @throws \Exception
     */
    public function getList(int $page): Paginator
    {
        $qb = $this->createQueryBuilder('contacts')
            ->select('contacts')
            ->orderBy('contacts.lastName', 'ASC')
        ;

        return (new Paginator($qb))->pagination($page);
    }
}
