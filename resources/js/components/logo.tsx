/**
 * Logo ALL4SPORT : l'emblème (ballons) suivi du nom de l'enseigne. Les images
 * proviennent des assets graphiques du projet (public/images).
 *
 * @param inverse Version claire du texte, pour un fond sombre.
 * @param className Classes supplémentaires du conteneur.
 */
export function Logo({
    inverse = false,
    className = '',
}: {
    inverse?: boolean;
    className?: string;
}) {
    return (
        <span className={`group inline-flex items-center gap-2 ${className}`}>
            <img
                src="/images/all4sport-logo.png"
                alt=""
                aria-hidden
                className="size-10 shrink-0 object-contain transition-transform duration-500 group-hover:scale-110 group-hover:-rotate-12"
            />
            <span
                className={`text-lg font-black tracking-tight italic ${inverse ? 'text-white' : 'text-navy dark:text-white'}`}
            >
                ALL<span className="text-primary">4</span>SPORT
            </span>
        </span>
    );
}
