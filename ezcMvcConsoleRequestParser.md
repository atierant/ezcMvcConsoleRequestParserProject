Problématique :
---------------

http://ezcomponents.org/docs/api/trunk/__filesource/fsource_MvcTools---MvcTools---src---request_parsers---http.php.html

ezcMvcHttpRequestParser est une classe du composant MVCTools des eZ Components.
La responsabilité de la classe ezcMvcHttpRequestParser est de transformer les variables d'environnement HTTP en un objet qui représente la requête dans le reste du processus.
Elle hérite de la classe ezcMvcRequestParser et implémente la méthode createRequest() qui retourne un objet ezcMvcRequest.
ezcMvcRequest est un objet contenant les variables d'environnement et naviguant entre les couches du MVC.	

En prenant cette classe comme exemple, nous allons créer un ezcMvcConsoleRequestParser.
La classe ezcMvcConsoleRequestParser demander doit transformer un appel en ligne de commande vers un objet requête ezcMvcRequest.

Les notions de get, post etc. n'ayant pas lieu d'être en console, nous aurons donc des $argv.

La CLI php est documentée au lien suivant : http://www.php.net/manual/fr/features.commandline.php

Objectif :
----------

Réaliser une classe qui transforme un appel en ligne de commande vers un objet requete ezcMvcRequest.
S'il y a des limites techniques sur certains aspects elles doivent être précisées. Par exemple, une URI qui sera toujours vide.

Mode opératoire :
-----------------
On fonctionnera en deux temps : d'abord en laissant vide ce qui est non récupérable, puis ensuite en rajoutant la possibilité de prendre en charge des arguments pour combler le manque.

Par exemple, le user agent, ou du accept encoding, risquent d'être difficiles à récupérer.
donc ne pas rajouter un --user-agent= de ta propre initiative. C'est tout à fait envisageable, mais qu'on fera éventuellement après.

Notes de documentation :
------------------------
_ignore_user_abort_  
Il est recommandé de définir ignore_user_abort pour les scripts en ligne de commande. Voir la fonction ignore_user_abort() pour plus d'informations : http://www.php.net/manual/fr/misc.configuration.php#ini.ignore-user-abort

_php -q_  

    --no-header
    -q             Quiet-mode. Suppress HTTP header output (CGI only).

_Les arguments de la CLI php_  
       args...        Arguments  passed  to  script.  Use '--' args when first
                      argument starts with '-' or script is read from stdin

$argv contient un tableau de tous les arguments passés au script lorsqu'il est appelé depuis la ligne de commande.

    Note: Le premier argument $argv[0] est toujours le nom qui a été utilisé pour exécuter le script. 
    Note: Cette variable n'est pas disponible lorsque register_argc_argv est désactivé. 

<?php
    var_dump($argv);
?>
 en exécutant :
php script.php arg1 arg2 arg3

on a ce retour :
    array(4) {
      [0]=>
      string(10) "script.php"
      [1]=>
      string(4) "arg1"
      [2]=>
      string(4) "arg2"
      [3]=>
      string(4) "arg3"
    }

On peut aussi utiliser getopt() pour lire les options passées dans la ligne de commande

On ne ne soucie pas de l'input.
Par contre il peut être intéressant de se pencher sur les flux I/O

-----------------------------

Check si on est en CLI :

if (PHP_SAPI != "cli") {
    exit;
}

Ou 

$cli_mode = false;
if ( isset($_SERVER['argc']) && $_SERVER['argc']>=1 ) {
  $cli_mode = true;
} 

-------------------------------
Exemple d'appel en ligne de commande à transformer en objet requête
php script.php 

--------------------------------
La classe est supposée être générique donc vérifier ce que php supporte

-------------------------------
Les options normalement récupérables par getopt(), les arguments (dans argv, leur nombre dans argc), les I/O
http://www.php.net/manual/fr/features.commandline.php#86940

-------------------------------
You can easily parse command line arguments into the $_GET variable by using the parse_str() function.

<?php

parse_str(implode('&', array_slice($argv, 1)), $_GET);

?>

It behaves exactly like you'd expect with cgi-php.

$ php -f somefile.php a=1 b[]=2 b[]=3

This will set $_GET['a'] to '1' and $_GET['b'] to array('2', '3').

-------------------------------

