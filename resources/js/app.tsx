import { createInertiaApp } from '@inertiajs/react';

/**
 * Point d'entrée du front-end React.
 *
 * Ce fichier est chargé par resources/views/app.blade.php (via `@vite`). Il
 * démarre Inertia, qui affiche le composant React demandé par le contrôleur
 * Laravel : `Inertia::render('auth/login')` charge resources/js/pages/auth/login.tsx.
 * Les pages sont retrouvées automatiquement à partir de leur nom.
 */

// Nom du site, défini par VITE_APP_NAME dans le fichier .env.
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

void createInertiaApp({
    // Titre de l'onglet du navigateur : "Titre de la page - ALL4SPORT".
    title: (title) => (title ? `${title} - ${appName}` : appName),
    // Barre de progression affichée pendant le chargement d'une page.
    progress: {
        color: '#4B5563',
    },
});
