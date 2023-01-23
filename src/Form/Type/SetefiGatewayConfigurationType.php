<?php

declare(strict_types=1);

namespace Filcronet\SyliusSetefiPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class SetefiGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('endpoint', TextType::class);
        $builder->add('terminalId', TextType::class);
        $builder->add('terminalPassword', TextType::class);
    }
}
