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

## Notice: SessionHandler::gc(): ps_files_cleanup_dir: opendir(/var/cpanel/php/sessions/ea-php82) failed: Permission denied (13)

### description: 

Lorsque je repasse en dev sur le serveur de prod (bluehost) cette erreur apparait

### Résolution

Changer le php ini temporaire => "session.save_path"
j'ai ajouté un dossier tmp dans var et fait pointé le session.save.path vers /var/tmp
Dans Bluehost on le trouve dans cpanel=>PHP=>php ini

## Debug bar ne s'affiche pas

config/packages/dev/web_profiler.yaml

passer la => toolbar: true
