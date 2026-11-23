<?php

declare(strict_types=1);

namespace Drupal\security_bulletin\Demo;

/**
 * FICTIONAL CONTENT FOR THE DEMONSTRATION SITE.
 *
 * ALL PRODUCTS, IDENTIFIERS AND LINKS ARE INVENTED: CVE IDS USE THE YEAR 2099
 * AND REFERENCES POINT TO EXAMPLE.COM, A DOMAIN RESERVED FOR DOCUMENTATION.
 */
final class DemoData {

  /**
   * TERMS OF THE PRODUCTS VOCABULARY.
   */
  public const PRODUCTS = [
    'Portail RH Mirabelle',
    'Passerelle VPN Cormoran',
    'Messagerie Pivoine',
    'Gestionnaire de fichiers Castor',
    'Annuaire Héron',
  ];

  /**
   * TERMS OF THE VULNERABILITY TYPES VOCABULARY.
   */
  public const VULNERABILITY_TYPES = [
    'Injection SQL',
    'Cross-site scripting (XSS)',
    'Contrôle d\'accès défaillant',
    'Exécution de code à distance',
    'Déni de service',
    'Divulgation d\'informations',
  ];

  /**
   * BULLETINS. AGE IS IN DAYS BEFORE THE COMMAND RUNS.
   */
  public const BULLETINS = [
    [
      'title' => 'Blocage du service par une pièce jointe malformée dans Messagerie Pivoine',
      'age' => 1,
      'product' => 'Messagerie Pivoine',
      'type' => 'Déni de service',
      'cve' => 'CVE-2099-0010',
      'score' => 3.7,
      'fix' => TRUE,
      'summary' => 'Une pièce jointe au format malformé provoque l\'arrêt du service d\'indexation de la messagerie, qui doit être redémarré manuellement.',
      'impact' => 'Un attaquant capable d\'envoyer un message à un utilisateur interne peut interrompre la recherche dans les messages jusqu\'au redémarrage du service. La consultation et l\'envoi restent disponibles.',
      'versions' => 'Versions 2.6.0 à 2.7.3.',
      'remediation' => 'Mettre à jour vers la version 2.7.4, qui ignore les pièces jointes dont l\'en-tête est invalide.',
      'reference' => 'pivoine-010',
    ],
    [
      'title' => 'Exécution de code via l\'aperçu des archives dans Gestionnaire de fichiers Castor',
      'age' => 3,
      'product' => 'Gestionnaire de fichiers Castor',
      'type' => 'Exécution de code à distance',
      'cve' => 'CVE-2099-0009',
      'score' => 9.1,
      'fix' => TRUE,
      'summary' => 'La génération de l\'aperçu d\'une archive déposée permet d\'écrire un fichier hors du dossier prévu, puis de le faire exécuter par le serveur.',
      'impact' => 'Un utilisateur authentifié disposant du droit de dépôt peut exécuter des commandes sur le serveur avec les droits du service, et accéder à l\'ensemble des fichiers stockés.',
      'versions' => 'Versions 4.0 à 4.3.1.',
      'remediation' => 'Mettre à jour vers la version 4.3.2, qui valide les chemins extraits des archives. En attendant, désactiver l\'aperçu des archives dans la configuration.',
      'reference' => 'castor-009',
    ],
    [
      'title' => 'Lecture de boîtes partagées sans délégation dans Messagerie Pivoine',
      'age' => 5,
      'product' => 'Messagerie Pivoine',
      'type' => 'Contrôle d\'accès défaillant',
      'cve' => 'CVE-2099-0008',
      'score' => 7.1,
      'fix' => TRUE,
      'summary' => 'L\'API de la messagerie ne vérifie pas les délégations lors de la lecture d\'une boîte partagée, ce qui permet de consulter des boîtes sans autorisation.',
      'impact' => 'Un utilisateur authentifié connaissant l\'adresse d\'une boîte partagée peut en lire les messages, y compris ceux des services RH ou juridiques.',
      'versions' => 'Versions 2.5.0 à 2.7.1.',
      'remediation' => 'Mettre à jour vers la version 2.7.2. Vérifier ensuite dans les journaux les accès aux boîtes partagées depuis la version 2.5.0.',
      'reference' => 'pivoine-008',
    ],
    [
      'title' => 'Déni de service par saturation des sessions dans Passerelle VPN Cormoran',
      'age' => 8,
      'product' => 'Passerelle VPN Cormoran',
      'type' => 'Déni de service',
      'cve' => 'CVE-2099-0007',
      'score' => 7.5,
      'fix' => FALSE,
      'summary' => 'Des tentatives de connexion répétées et incomplètes épuisent la table des sessions de la passerelle et bloquent les connexions légitimes.',
      'impact' => 'Un attaquant non authentifié peut empêcher les salariés en télétravail de se connecter au réseau interne tant que l\'attaque dure.',
      'versions' => 'Toutes les versions jusqu\'à la 5.2.9 incluse.',
      'remediation' => 'Aucun correctif n\'est disponible à ce jour. En attendant, limiter le nombre de connexions par adresse IP sur le pare-feu placé devant la passerelle.',
      'reference' => 'cormoran-007',
    ],
    [
      'title' => 'Accès aux fichiers d\'autres utilisateurs par identifiant prévisible dans Gestionnaire de fichiers Castor',
      'age' => 11,
      'product' => 'Gestionnaire de fichiers Castor',
      'type' => 'Contrôle d\'accès défaillant',
      'cve' => 'CVE-2099-0006',
      'score' => 6.5,
      'fix' => TRUE,
      'summary' => 'Les liens de téléchargement utilisent des identifiants séquentiels, sans vérifier que le demandeur a accès au fichier.',
      'impact' => 'Un utilisateur authentifié peut télécharger les fichiers des autres utilisateurs en modifiant l\'identifiant dans l\'adresse du lien.',
      'versions' => 'Versions 4.0 à 4.2.5.',
      'remediation' => 'Mettre à jour vers la version 4.2.6, qui contrôle les droits à chaque téléchargement.',
      'reference' => 'castor-006',
    ],
    [
      'title' => 'Exposition des attributs internes dans Annuaire Héron',
      'age' => 14,
      'product' => 'Annuaire Héron',
      'type' => 'Divulgation d\'informations',
      'cve' => 'CVE-2099-0005',
      'score' => 4.3,
      'fix' => TRUE,
      'summary' => 'La recherche de l\'annuaire renvoie des attributs internes des comptes, dont la date du dernier changement de mot de passe.',
      'impact' => 'Un utilisateur authentifié peut repérer les comptes dont le mot de passe n\'a pas changé depuis longtemps, et cibler ces comptes lors d\'une attaque.',
      'versions' => 'Versions 1.8 à 1.9.4.',
      'remediation' => 'Mettre à jour vers la version 1.9.5, qui filtre les attributs renvoyés par la recherche.',
      'reference' => 'heron-005',
    ],
    [
      'title' => 'Divulgation de la version du serveur dans Portail RH Mirabelle',
      'age' => 18,
      'product' => 'Portail RH Mirabelle',
      'type' => 'Divulgation d\'informations',
      'cve' => 'CVE-2099-0004',
      'score' => 3.1,
      'fix' => TRUE,
      'summary' => 'Les en-têtes des réponses HTTP indiquent la version exacte du portail et de ses composants.',
      'impact' => 'Un attaquant peut savoir précisément quelles failles connues s\'appliquent au portail, ce qui facilite la préparation d\'une attaque.',
      'versions' => 'Toutes les versions jusqu\'à la 3.4.3 incluse.',
      'remediation' => 'Mettre à jour vers la version 3.4.4, qui retire ces en-têtes par défaut.',
      'reference' => 'mirabelle-004',
    ],
    [
      'title' => 'Exécution de code à distance dans le portail d\'authentification de Passerelle VPN Cormoran',
      'age' => 21,
      'product' => 'Passerelle VPN Cormoran',
      'type' => 'Exécution de code à distance',
      'cve' => 'CVE-2099-0003',
      'score' => 9.8,
      'fix' => TRUE,
      'summary' => 'Un dépassement de tampon dans le traitement des requêtes de connexion permet à un attaquant non authentifié d\'exécuter du code arbitraire sur la passerelle, depuis Internet.',
      'impact' => 'La prise de contrôle complète de la passerelle est possible sans identifiant ni interaction d\'un utilisateur. L\'équipement étant exposé sur Internet, il peut servir de point d\'entrée vers le réseau interne.',
      'versions' => 'Versions 5.0 à 5.2.8.',
      'remediation' => 'Mettre à jour sans délai vers la version 5.2.9. Si la mise à jour est impossible dans l\'immédiat, restreindre l\'accès au portail d\'authentification aux adresses IP connues.',
      'reference' => 'cormoran-003',
    ],
    [
      'title' => 'XSS stocké dans la signature des messages de Messagerie Pivoine',
      'age' => 22,
      'product' => 'Messagerie Pivoine',
      'type' => 'Cross-site scripting (XSS)',
      'cve' => 'CVE-2099-0002',
      'score' => 5.3,
      'fix' => FALSE,
      'summary' => 'Un défaut de filtrage de la signature permet à un utilisateur authentifié d\'injecter du code JavaScript, exécuté dans le navigateur des destinataires qui ouvrent le message.',
      'impact' => 'Un attaquant disposant d\'un compte peut exécuter du code JavaScript dans la session d\'un destinataire et lire les messages affichés à l\'écran. L\'exploitation exige que la victime ouvre le message piégé.',
      'versions' => 'Toutes les versions jusqu\'à la 2.7.1 incluse.',
      'remediation' => 'Aucun correctif n\'est disponible à ce jour. En attendant, désactiver les signatures au format HTML dans les paramètres d\'administration.',
      'reference' => 'pivoine-002',
    ],
    [
      'title' => 'Injection SQL dans la recherche du Portail RH Mirabelle',
      'age' => 24,
      'product' => 'Portail RH Mirabelle',
      'type' => 'Injection SQL',
      'cve' => 'CVE-2099-0001',
      'score' => 8.8,
      'fix' => TRUE,
      'summary' => 'Une injection SQL dans le formulaire de recherche permet à un utilisateur authentifié de lire des données de la base.',
      'impact' => 'Un utilisateur authentifié peut injecter des commandes SQL dans le formulaire de recherche et lire le contenu de tables auxquelles il n\'a pas accès, dont les données personnelles des salariés.',
      'versions' => 'Versions 3.0 à 3.4.2.',
      'remediation' => 'Mettre à jour vers la version 3.4.3, qui utilise des requêtes préparées. En attendant, désactiver la recherche avancée.',
      'reference' => 'mirabelle-001',
    ],
  ];

  /**
   * BASIC PAGES LINKED FROM THE FOOTER MENU, IN MENU ORDER.
   */
  public const PAGES = [
    [
      'title' => 'À propos',
      'alias' => '/a-propos',
      'body' => '<p>Ce portail publie des bulletins de sécurité sur les produits d\'un parc informatique fictif. Chaque bulletin est rédigé, puis validé par l\'équipe sécurité avant sa publication.</p>'
      . '<h2>Un projet d\'apprentissage</h2>'
      . '<p>Ce site est un projet de démonstration construit avec Drupal 11 : modèle de contenu, workflow éditorial, thème sur mesure et module développé pour l\'occasion. Son code source est public.</p>'
      . '<p><a href="https://github.com/audreyantony/drupal-security-bulletins">Voir le code source sur GitHub</a></p>'
      . '<h2>Contenu fictif</h2>'
      . '<p>Les produits, les vulnérabilités et les identifiants CVE présentés ici sont inventés. Ils ne décrivent aucun logiciel réel.</p>',
    ],
    [
      'title' => 'Mentions légales',
      'alias' => '/mentions-legales',
      'body' => '<h2>Éditeur</h2>'
      . '<p>[Nom de l\'éditrice], projet personnel non commercial.</p>'
      . '<h2>Hébergement</h2>'
      . '<p>[Nom, adresse et téléphone de l\'hébergeur]</p>'
      . '<h2>Données personnelles</h2>'
      . '<p>En dehors des journaux techniques du serveur, conservés pour sa sécurité, ce site ne collecte aucune donnée personnelle et ne dépose aucun cookie de mesure d\'audience. Un cookie de session est déposé uniquement lors de la connexion d\'un compte de rédaction.</p>'
      . '<h2>Contenu</h2>'
      . '<p>Les produits et bulletins de sécurité présentés sur ce site sont fictifs.</p>',
    ],
  ];

}
