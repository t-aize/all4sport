<?php

namespace App\Http\Controllers;

/**
 * Classe de base de tous les contrôleurs de l'application.
 *
 * Un contrôleur reçoit une requête HTTP (déclarée dans routes/), fait le
 * travail demandé (valider, lire ou écrire en base…) puis renvoie une réponse :
 * une page Inertia, une redirection, du JSON, etc.
 *
 * Cette classe est volontairement vide : elle sert de point d'extension si l'on
 * veut un jour partager une méthode entre tous les contrôleurs.
 */
abstract class Controller
{
    //
}
