Principe de base :
------------------

- Un flux d'informations est reçu, via un protocole de transfert particulier (http, smtp...)
- La pile TCP/IP reconstitue le message
- Le message est transmis au serveur pour être traité, en parsant le message.
- Dans le cas particulier d'un CLI, qui nest pas vraiment un protocole à part entière de transfert de message, on passe par un interpréteur qui est devant.

Illustration par l'exemple : cURL.

Lorsqu'on fait une requête http via CURL, la réponse qui est envoyé est une chaine de caractère commençant par :

	HTTP/1.1 302 Found
	Location: http://www.google.fr/
	Cache-Control: private
	Content-Type: text/html; charset=UTF-8
	Set-Cookie: PREF=ID=052d8162b254e32f:FF=0:TM=1338538811:LM=1338538811:S=7K0IjJglvdD8ZP_4; expires=Sun, 01-Jun-2014 08:20:11 GMT; path=/; domain=.google.com
	Set-Cookie: NID=60=IETb1w3CEk8WpU1189N77yY4s6t3WWvI7gYd3IkMdci2WNB1At4YD8CbokbSNKMLdTdjybjU0znZMZrY46Nf2EizXh1Kkz_7PjmlS3ppRGaUD5JDdSMznX8r42IhgvYP; expires=Sat, 01-Dec-2012 08:20:11 GMT; path=/; domain=.google.com; HttpOnly
	P3P: CP="This is not a P3P policy! See http://www.google.com/support/accounts/bin/answer.py?hl=en&answer=151657 for more info."
	Date: Fri, 01 Jun 2012 08:20:11 GMT
	Server: gws
	Content-Length: 218
	X-XSS-Protection: 1; mode=block
	X-Frame-Options: SAMEORIGIN

	HTTP/1.1 200 OK
	Date: Fri, 01 Jun 2012 08:20:12 GMT
	Expires: -1
	Cache-Control: private, max-age=0
	Content-Type: text/html; charset=ISO-8859-1
	Set-Cookie: PREF=ID=ea7b372feca421a2:FF=0:TM=1338538812:LM=1338538812:S=EXnJ3T8rt0Z3NpzM; expires=Sun, 01-Jun-2014 08:20:12 GMT; path=/; domain=.google.fr
	Set-Cookie: NID=60=KJ96GUXdvX-MBVc9nTYr_tzh6hSB3Ryhtz0ww1oxkS1viD0ZzvUMO4i017xjD7Q3WgTTAx8FjtyfpvkEXY9x8JTlLQ2UgNSFQdO6fZUX33CQwsJjKE_7w0_1xH03fChj; expires=Sat, 01-Dec-2012 08:20:12 GMT; path=/; domain=.google.fr; HttpOnly
	P3P: CP="This is not a P3P policy! See http://www.google.com/support/accounts/bin/answer.py?hl=en&answer=151657 for more info."
	Server: gws
	X-XSS-Protection: 1; mode=block
	X-Frame-Options: SAMEORIGIN
	Transfer-Encoding: chunked

	<!doctype html>[...]


On voit dans la réponse : 

	+ HTTP/1.1 (le protocole)
	+ plusieurs lignes d'en-tete  clé: valeur (exemple : Cache-Control: private)
	+ puis deux retours à la ligne
	+ puis le corps

Il est nécessaire de parser ce message pour récupérer d'un côté les en-têtes, et de l'autre le corps.
Dans cet exemple cas il s'agit d'une réponse reçue, pas d'une requête qu'on demande de traiter, mais le mécanisme est le même : un message est reçu, il faut le décomposer. Peu importe le protocole, que ce soit du http, du webdav, du mysql ou tout autre protocole...

A l'arrivée on reçoit toujours un message qui contient une requête. Comme on sait que s'il y a quelque chose de commun, il faut le factoriser. On fait donc un objet Requete à l'aide d'adaptateurs qui sont fonction du protocole. Peu importe d'où provient la ressource, l'important est de la comprendre et de la standardiser pour pouvoir l'exploiter librement.

Si par exemple je demande la suppression d'un élément, par exemple la suppression d'un profil utilisateur, savoir comment l'ordre a été donné, dans quelle language, dans quel protocole, n'a pas d'importance. Le déclenchement de l'action doit être indépendant du protocole.
Le contrôleur reçoit un objet contenant suffisament de données pour que le routage se fasse. Tout ce qui l'intéresse c'est un ordre et une action à effectuer en fonction de cet ordre.
Cependant, des cas particulier peuvent être envisageables si le protocole et le mode d'intéraction sont tellement liés qu'à ce moment là on a deux types de requêtes et donc deux classes de requetes différentes.

Le protocole ne sert que d'implémentation pour envoyer des ordres. A la base il y a un ordre que quelqu'un veut passer. Cet ordre est écrit dans un format, transféré, décrypté puis exécuté. Tant que le type d'ordre est identique et ne diffère que par le protocole, on peut toujours reconstituer un objet de type identique à tous les cas puisqu'il représente ce que la personne voulait avant le transfert.

Le principal souci pour notre cas de ligne de commande est d'avoir un adaptateur/interpréteur assez souple pour interpréter correctement la ligne de commande émise. Dans les protocoles http, smtp, les paramètres & ordres sont bien définis. 
Notre classe n'a pas la responsabilité de vérifier l'information reçue, seulement de récupérer ce qui est récupérable, de standardiser l'information dans un objet (ezcMvcRequest) qu'elle aura réussi à construire, et de transmettre cet objet.
La question à se poser est : "Quelles sont les informations disponibles en CLI et dans quel partie d'une requête elles peuvent se ranger ?"
Nous avons un un système s'intérrogeant à la base par du http. en correspondance nous avons une classe qui parse la requête HTTP et la transforme en objet. Nous voulons avoir un système s'intérrogeant via la console.

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

Repository du projet :
----------------------

https://github.com/atierant/ezcMvcConsoleRequestParserProject

Notes de documentation :
------------------------
_ignore_user_abort_  
Il est recommandé de définir ignore_user_abort pour les scripts en ligne de commande. Voir la fonction ignore_user_abort() pour plus d'informations : http://www.php.net/manual/fr/misc.configuration.php#ini.ignore-user-abort

-------------------------------
_php -q_  

    --no-header
    -q             Quiet-mode. Suppress HTTP header output (CGI only).

-------------------------------
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

Il peut être intéressant de se pencher sur les flux I/O

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
Exemple d'appel en ligne de commande à transformer en objet requête :  

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
read arguments from $argv of the form --name=VALUE and -flag.
http://www.php.net/manual/fr/features.commandline.php#86616  

To allow a "zero" option value : http://www.php.net/manual/fr/features.commandline.php#86130  

________________________________


Commentaires sur les classes existantes :
-----------------------------------------
1. La classe ezcMvcRequest, objet de transition
-----------------------------------------------

- http://ezcomponents.org/docs/api/trunk/MvcTools/ezcMvcRequest.html
- L'objet de requête contient les données de la requête
- Il doit être créé par le parseur de requêtes en premier lieu. Il peut également être retourné par le contrôleur, dans le cas d'une redirection interne.
- Il est composé des données dépendantes du protocole dans l'objet ezcMvcRawRequest, dans sa propriété $raw.
- Il détient aussi plusieurs structures qui contiennent des données faisant abstraction du protocole de provenance.
- Ces données sont stockées dans les propriétés suivantes :
    + $files: tableau d'instances de ezcMvcRequestFile.
    + $cache: instance de ezcMvcRequestCache
    + $content: instance de ezcMvcRequestContent
    + $agent: instance de ezcMvcRequestAgent
    + $authentication: instance de ezcMvcRequestAuthentication
- Il contient les variables de la requête dans un tableau $request[clé]=>valeur.


2. La classe mère ezcMvcRequestParser
-------------------------------------
- ...
- ...

3. La classe ezcMvcHttpRequestParser
------------------------------------
- ...
- ...

4. La classe ezcMvcMailRequestParser
-------------------------------------
- La classe n'est pas dans le MVCTools car elle n'est pas indispensable. Elle se situe dans le package MvcMailTiein
- ...

5. Notre classe ezcMvcConsoleRequestParser
------------------------------------------
- Avec quoi la peupler au vu de ce qui a été observé sur les classes précédentes ?
- Qu'est-ce qui est pertinent ?
- Qu'est-ce qu'il est possible d'obtenir via le serveur
- Qu'est-ce qu'il est possible d'obtenir via les paramètres d'une LDC

-----------------------------------

To Do :
-------

1. Vérifier qu'on soit bien en mode CLI et que les arguments soient autorisés (cf. partie "Check si on est en CLI")
2. Initialiser l'objet de retour ezcMvcRequest
3. Parser les exec/options/flags/arguments de la ligne de commande (+ possibilité de n'avoir rien de renvoyé dans la LDC, cf. zero option value)
4. En fonction des retours, peupler l'objet ezcMvcRequest
5. 
