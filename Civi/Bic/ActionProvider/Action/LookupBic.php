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

use CRM_Bic_ExtensionUtil as E;
use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

class LookupBic extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return \Civi\ActionProvider\Parameter\SpecificationBag specs
   */
  public function getConfigurationSpecification() {
    $normalise_options = [
      '0' => E::ts('No'),
      '1' => E::ts('Yes'),
    ];

    return new SpecificationBag([
      new Specification('normalise', 'Integer', E::ts('Normalise IBAN'), FALSE, NULL, NULL, $normalise_options, FALSE),
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
      new Specification('iban', 'String', E::ts('IBAN'), TRUE),
      new Specification('bic', 'String', E::ts('BIC (pass-trough if not found)'), FALSE),
    ]);
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * @return \Civi\ActionProvider\Parameter\SpecificationBag specs
   */
  public function getOutputSpecification() {
    return new SpecificationBag([
      new Specification('iban', 'String', E::ts('IBAN'), TRUE, NULL, NULL, NULL, FALSE),
      new Specification('bic', 'String', E::ts('BIC'), FALSE, NULL, NULL, NULL, FALSE),
      new Specification('title', 'String', E::ts('Title'), FALSE, NULL, NULL, NULL, FALSE),
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
    $iban = $parameters->getParameter('iban');

    // normalise
    $normalise = $this->configuration->getParameter('normalise');
    if ($normalise) {
      $iban = strtoupper($iban);
      $iban = preg_replace('/\W/', '', $iban);
    }
    $output->setParameter('iban', $iban);

    // verify
    $valid = (bool) preg_match('/^[A-Z0-9]+$/', $iban);
    // FIXME: additional validation?

    // look up BIC
    $lookup = \civicrm_api3('Bic', 'findbyiban', ['iban' => $iban]);

    // act
    if (empty($lookup['bic'])) {
      // not found: pass through input BIC
      $output->setParameter('bic', $parameters->getParameter('bic'));
      $output->setParameter('error', E::ts('BIC not found/verified'));
    }
    else {
      // BIC found
      $output->setParameter('bic', $lookup['bic']);
      $output->setParameter('title', $lookup['title']);
      $output->setParameter('error', '');
    }
  }

}
