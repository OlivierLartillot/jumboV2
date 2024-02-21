
## dans twig

```twig
    {{ app.request.server.get('MA_VARIABLE') }}
```

## dans le controlleur

### Service.yaml
On peut s'en passer si on utilise le php Natif $_ENV mais "tout sera string"

On peut typer en utilisatnt symfony: Il suffit d un peu de config:

<i>*Le mot clé app. n est pas du tout obligatoire !!!</i>
```yaml
    # Cool, On peut Typer la variable !!!
    app.maVariable: '%env(bool:MA_VARIABLE)%' 

    # Je peux aussi définir ici directement une variable d'env !
    app.uneAutreVariable: true 
```
### controlleur
```php
    $this->getParameter('app.maVariable'); // récupéré de service YAML
    $_ENV['MA_VARIABLE']; // Ne peut etre que type string
```