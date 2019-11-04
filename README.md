# Installation
Le plugin peut être installé via l’installateur d’extensions de wordpress (uploader un fichier zip)

# Paramètres globaux
Le plugin a une interface setting qui permet de cocher les post types concernés par le rafraichissement (activer/désactiver le rafraichissement), un champ valeur permet de saisir la fréquence par défaut qui sera proposée à l’utilisateur dans chaque post.
Une case à cocher permet de sélectionner le type de date à mettre à jour : date de création, date de publication, date de mise à jour (la date de mise à jour est toujours sélectionnée et ne peut pas être désélectionnée).
Un bouton « Enregistrer » permet de valider ces paramètres globaux.

# Paramètre dans chaque post
Le plugin ajoute un bloc dans l’admin de chaque posts concerné par le rafraichissement. Le bloc pourrait se situer si possible juste en dessous du bloc de date de publication.
Le bloc nommé « Rafraîchir la date automatiquement tous les X jours » dispose d’une case à cocher qui permet permet d’activer ou désactiver le rafraichissement pour ce post. La fréquence de rafraichissement n’est pas éditable individuellement. Un lien « Réglages » discret situé juste en dessous permet d’accéder aux réglages globaux.
En dessous en italique on écrit (exemple) : « La date de création, publication et mise à jour sera rafraîchie automatiquement dans 3 jours »

# Cron
Chaque nuit une tâche sélectionne tous les posts dont la fraîcheur est plus ancienne que celle qui est définie et modifie leur date de mise à jour.
Exemple pour un post avec une fréquence réglée à 7 jours et dont les dates de publication et de mise à jour sont rafraîchissables :
si date de publication est rafraîchissables et qu’elle est plus ancienne que 7 jours, alors mettre à la date d’aujourd’hui, sans changer l’heure / sinon ne rien faire
si date de mise à jour est rafraîchissables et qu’elle est plus ancienne que 7 jours, alors mettre à la date d’aujourd’hui, sans changer l’heure / sinon ne rien faire

La durée total de processus du cron ne doit pas dépasser 10 secondes et se dérouler la nuit vers 02:00 du matin.
Si la durée total dépasse 10 secondes, la tâche doit être suspendue et reprendre plus tard afin de ne pas générer de rupture de service.