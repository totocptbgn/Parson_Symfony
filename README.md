# Projet de PW6
> Projet de Programmation Web, construction d'un site permettant la création et la résolution du problème de Parson.
> Thomas Bignon, Groupe C, 21701742.

## Détails et Structure
J'ai réalisé ce projet en PHP pour le côté serveur et du JavaScript pour le côté client. Voici les bibliothèques et frameworks utilisés :

- [Symfony v4.14.3](https://symfony.com/)
- [Bootstrap v4.3.1](https://getbootstrap.com/)
- [jQuery v3.3.1](https://jquery.com/)
- [jQuery LinedTextArea](https://www.jqueryscript.net/form/lined-textarea.html) - pour ajouter des lignes dans le TextArea permettant d'écrire le code.
- [SortableJS](https://github.com/SortableJS/Sortable) - pour implementer le Drag & Drop d'éléments d'une liste.

Il y a 3 Controllers,
- `SecurityController.php` - gère les pages qui gèrent la connection, la création de compte...
- `StudentController.php` - gère les pages accessibles par les étudiants.
- `TeacherController.php` - gère les pages accessibles par les professeurs.
- `ErrorController.php` - gère les erreurs internes du serveur.

et 4 Entitys :
- `User.php` - représente un utilisateur.
- `Course.php` - représente un cours.
- `Exercice.php` - représente un exercice.
- `Attempt.php` - représente un Essai.

Le visionnage des résultats se fait directement dans les menus pour les cours et exercices. Les résultats sont indiqués dans des petits badges sur la droites pour les profs. Pour les resultats par élève, ils sont accessibles via la page home des profs.

## Points Faibles
J'ai implémenté la totalité des critères au niveau optimal. J'ai réalisé ce projet seul, et nous avons beaucoup d'autres projets en parallèle. Il y a donc plusieurs points qui, je reconnais, mériteraient des améliorations. Je fais beaucoup d'opérations côté client que je devrais aussi faire côté serveur (notamment des vérifications des entrées des formulaires), si un utilisateur modifie le JavaScript dans son navigateur, il peut très facilement casser beaucoup de chose. Un élève n'a pas à s'embêter avec l'indentation, elle est automatique. On m'a prévenu que la notation sera plus clémente si on faisait le projet seul.

## Points Forts
J'ai construis l'authentification grâce la commande `symfony make:auth`, ainsi les mots de passe sont chiffrés et l'utilisateur est directement connecté et "authentifié" par Symfony.
La vérification de la réussite de l'exercice se fait avec le Javascript mais la publication des résultats se fait asynchroniquement, comme demandé. D'ailleurs, la page attends une réponse à la requête AJAX pour montrer les résultats.
J'ai aussi utilisé des requêtes asynchrones pour la création du compte : lorsqu'un nouvel utilisateur rentre son username, la page va vérifier en arrière-plan que le username n'est pas déjà utilisé.
Lorsqu'une erreur interne arrive, au lieu d'afficher la stacktrace de Symfony, j'ai créer un controller pour gérer l'exception et l'afficher sur une page respectant les codes de mon site.

