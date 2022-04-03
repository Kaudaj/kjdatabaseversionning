<?php
/**
 * Copyright since 2019 Kaudaj
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@kaudaj.com so we can send you a copy immediately.
 *
 * @author    Kaudaj <info@kaudaj.com>
 * @copyright Since 2019 Kaudaj
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

declare(strict_types=1);

namespace Kaudaj\Module\DBVCS\ConstraintValidator\Factory;

use PrestaShop\PrestaShop\Core\ConstraintValidator\CleanHtmlValidator;
use PrestaShop\PrestaShop\Core\ConstraintValidator\Constraints\CleanHtml;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\ConstraintValidatorInterface;

class CleanHtmlValidatorFactory extends ConstraintValidatorFactory implements ConstraintValidatorFactoryInterface
{
    /**
     * @var bool
     */
    private $allowEmbeddableHtml;

    /**
     * CleanHtmlValidatorFactory constructor.
     */
    public function __construct(bool $allowEmbeddableHtml)
    {
        parent::__construct();

        $this->allowEmbeddableHtml = $allowEmbeddableHtml;
    }

    /**
     * @param Constraint $constraint
     *
     * @return ConstraintValidatorInterface
     */
    public function getInstance(Constraint $constraint)
    {
        if ($constraint instanceof CleanHtml) {
            return new CleanHtmlValidator($this->allowEmbeddableHtml);
        }

        return parent::getInstance($constraint);
    }
}
