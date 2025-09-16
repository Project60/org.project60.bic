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

namespace Civi\Bic\ActionProvider\Action;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

use CRM_Sepa_ExtensionUtil as E;

class VerifyBIC extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return \Civi\ActionProvider\Parameter\SpecificationBag specs
   */
  public function getConfigurationSpecification() {
    $normalise_options = [
      '0' => E::ts('leave as is'),
      '1' => E::ts('normalise to upper case, no spaces'),
    ];

    $invalid_bic_options = [
      'pass_trough' => E::ts('pass through as is'),
      'erase'       => E::ts('set to empty'),
    ];

    return new SpecificationBag([
      new Specification('normalise', 'Integer', E::ts('Normalisation'), FALSE, NULL, NULL, $normalise_options, FALSE),
      new Specification('invalid', 'String', E::ts('If BIC invalid'), FALSE, NULL, NULL, $invalid_bic_options, FALSE),
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return \Civi\ActionProvider\Parameter\SpecificationBag specs
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
        // required fields
      new Specification('bic', 'String', E::ts('BIC (to check)'), TRUE),
    ]);
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * @return \Civi\ActionProvider\Parameter\SpecificationBag specs
   */
  public function getOutputSpecification() {
    return new SpecificationBag([
      new Specification('bic', 'String', E::ts('BIC'), FALSE, NULL, NULL, NULL, FALSE),
      new Specification('error', 'String', E::ts('Error'), FALSE, NULL, NULL, NULL, FALSE),
    ]);
  }

  /**
   * Run the action
   *
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $output
   *     The parameters this action can send back
   *
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    // get BIC
    $bic = $parameters->getParameter('bic');

    // normalise
    $normalise = $this->configuration->getParameter('normalise');
    if ($normalise) {
      $bic = strtoupper($bic);
      $bic = preg_replace('/\s/', '', $bic);
    }

    // verify
    $valid = (bool) preg_match('/^[A-Z]{6}[A-Z2-9][A-NP-Z0-9]([A-Z0-9]{3})?$/', $bic);

    // apply
    if ($valid) {
      $output->setParameter('bic', $bic);
      $output->setParameter('error', '');

    }
    else {
      $invalid = $this->configuration->getParameter('invalid');
      switch ($invalid) {
        case 'pass_trough':
          $output->setParameter('bic', $bic);
          break;

        default:
        case 'erase':
          $output->setParameter('bic', '');
          break;
      }
      $output->setParameter('error', E::ts("BIC '%1' invalid", [1 => $bic]));
    }
  }

}
