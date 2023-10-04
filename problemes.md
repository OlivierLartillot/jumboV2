# Fichier qui liste les problèmes courant sur ce serveur

## 503 !

### description:

Souvent après une période ou cela fonctionne le serveur crash sur la page en question. Le cache:clear résout le problème provisoirement mais le problème revient rapidement.

### Résolution

Apres avoir supprimé la vue et remonté progressivement dans le controleur (mise de code en commentaire), c'est encore une fois une query qui étaient problématique.

> observe les guillemets :
```php
    ->groupBy('c.staff, c.flightNumber') ...
```

au lieu de 
```php
    ->groupBy('c.staff', 'c.flightNumber') ...
```
