<?php

namespace App\Models;

/**
 * Classe Personne - Classe de base pour les personnes
 * Attributs: nom, prenom, age
 */
class Personne
{
    protected $nom;
    protected $prenom;
    protected $age;

    /**
     * Constructeur de la classe Personne
     * @param string $nom
     * @param string $prenom
     * @param int $age
     */
    public function __construct($nom, $prenom, $age)
    {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->age = $age;
    }

    /**
     * Méthode afficheur - affiche les informations de la personne
     */
    public function afficher()
    {
        echo "Nom: " . $this->nom . "\n";
        echo "Prénom: " . $this->prenom . "\n";
        echo "Age: " . $this->age . "\n";
    }

    // Getters
    public function getNom()
    {
        return $this->nom;
    }

    public function getPrenom()
    {
        return $this->prenom;
    }

    public function getAge()
    {
        return $this->age;
    }
}
