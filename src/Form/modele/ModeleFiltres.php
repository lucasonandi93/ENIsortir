<?php

namespace App\Form\modele;

use App\Entity\Campus;

class ModeleFiltres
{

    private ?Campus $campus = null;
    private  ?string $nom = null;
    private ?bool $sortieOrganisateur = null;
    private ?bool $sortieInscrit = null;
    private ?bool $sortiePasInscrit = null;
    private ?bool $sortiePasses = null;

    private ?\DateTime $DateSortie = null;
    private ?\DateTime $DateCloture = null;

    public function __construct()
{
}

    /**
     * @return Campus|null
     */
    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    /**
     * @param Campus|null $campus
     */
    public function setCampus(?Campus $campus): void
    {
        $this->campus = $campus;
    }

    /**
     * @return string|null
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * @param string|null $nom
     */
    public function setNom(?string $nom): void
    {
        $this->nom = $nom;
    }

    /**
     * @return bool|null
     */
    public function getSortieOrganisateur(): ?bool
    {
        return $this->sortieOrganisateur;
    }

    /**
     * @param bool|null $sortieOrganisateur
     */
    public function setSortieOrganisateur(?bool $sortieOrganisateur): void
    {
        $this->sortieOrganisateur = $sortieOrganisateur;
    }

    /**
     * @return bool|null
     */
    public function getSortieInscrit(): ?bool
    {
        return $this->sortieInscrit;
    }

    /**
     * @param bool|null $sortieInscrit
     */
    public function setSortieInscrit(?bool $sortieInscrit): void
    {
        $this->sortieInscrit = $sortieInscrit;
    }

    /**
     * @return bool|null
     */
    public function getSortiePasInscrit(): ?bool
    {
        return $this->sortiePasInscrit;
    }

    /**
     * @param bool|null $sortiePasInscrit
     */
    public function setSortiePasInscrit(?bool $sortiePasInscrit): void
    {
        $this->sortiePasInscrit = $sortiePasInscrit;
    }

    /**
     * @return bool|null
     */
    public function getSortiePasses(): ?bool
    {
        return $this->sortiePasses;
    }

    /**
     * @param bool|null $sortiePasses
     */
    public function setSortiePasses(?bool $sortiePasses): void
    {
        $this->sortiePasses = $sortiePasses;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateSortie(): ?\DateTime
    {
        return $this->DateSortie;
    }

    /**
     * @param \DateTime|null $DateSortie
     */
    public function setDateSortie(?\DateTime $DateSortie): void
    {
        $this->DateSortie = $DateSortie;
    }


    /**
     * @return \DateTime|null
     */
    public function getDateCloture(): ?\DateTime
    {
        return $this->DateCloture;
    }

    /**
     * @param \DateTime|null $DateCloture
     */
    public function setDateCloture(?\DateTime $DateCloture): void
    {
        $this->DateCloture = $DateCloture;
    }




}