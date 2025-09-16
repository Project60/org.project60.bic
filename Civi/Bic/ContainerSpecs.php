<?php
/*-------------------------------------------------------+
| Project 60 - Little BIC extension                      |
| Copyright (C) 2020                                     |
| Author: B. Endres (endres -at- systopia.de)            |
| http://www.systopia.de/                                |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

declare(strict_types = 1);

namespace Civi\Bic;

use CRM_Bic_ExtensionUtil as E;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContainerSpecs implements CompilerPassInterface {

  /**
   * {@inheritDoc}
   */
  public function process(ContainerBuilder $container) {
    if (!$container->hasDefinition('action_provider')) {
      return;
    }
    $typeFactoryDefinition = $container->getDefinition('action_provider');
    $typeFactoryDefinition->addMethodCall(
      'addAction',
      [
        'LookupBIC',
        'Civi\Bic\ActionProvider\Action\LookupBic',
        E::ts('Look up BIC for IBAN'),
        [\Civi\ActionProvider\Action\AbstractAction::DATA_RETRIEVAL_TAG],
      ]
    );
    $typeFactoryDefinition->addMethodCall(
      'addAction',
      [
        'VerifyBIC',
        'Civi\Bic\ActionProvider\Action\VerifyBic',
        E::ts('Verify BIC'),
        [\Civi\ActionProvider\Action\AbstractAction::DATA_RETRIEVAL_TAG],
      ]
    );
  }

}
