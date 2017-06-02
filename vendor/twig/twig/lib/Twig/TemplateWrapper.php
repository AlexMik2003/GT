<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Exposes a templates to userland.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class Twig_TemplateWrapper
{
    private $env;
    private $template;

    /**
     * This method is for internal use only and should never be called
     * directly (use Twig_Environment::load() instead).
     *
     * @internal
     */
    public function __construct(Twig_Environment $env, Twig_Template $template)
    {
        $this->env = $env;
        $this->template = $template;
    }

    /**
     * Renders the templates.
     *
     * @param array $context An array of parameters to pass to the templates
     *
     * @return string The rendered templates
     */
    public function render($context = array())
    {
        return $this->template->render($context);
    }

    /**
     * Displays the templates.
     *
     * @param array $context An array of parameters to pass to the templates
     */
    public function display($context = array())
    {
        $this->template->display($context);
    }

    /**
     * Checks if a block is defined.
     *
     * @param string $name    The block name
     * @param array  $context An array of parameters to pass to the templates
     *
     * @return bool
     */
    public function hasBlock($name, $context = array())
    {
        return $this->template->hasBlock($name, $context);
    }

    /**
     * Returns defined block names in the templates.
     *
     * @param array $context An array of parameters to pass to the templates
     *
     * @return string[] An array of defined templates block names
     */
    public function getBlockNames($context = array())
    {
        return $this->template->getBlockNames($context);
    }

    /**
     * Renders a templates block.
     *
     * @param string $name    The block name to render
     * @param array  $context An array of parameters to pass to the templates
     *
     * @return string The rendered block
     */
    public function renderBlock($name, $context = array())
    {
        $context = $this->env->mergeGlobals($context);
        $level = ob_get_level();
        ob_start();
        try {
            $this->template->displayBlock($name, $context);
        } catch (Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $e;
        } catch (Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $e;
        }

        return ob_get_clean();
    }

    /**
     * Displays a templates block.
     *
     * @param string $name    The block name to render
     * @param array  $context An array of parameters to pass to the templates
     */
    public function displayBlock($name, $context = array())
    {
        $this->template->displayBlock($name, $this->env->mergeGlobals($context));
    }

    /**
     * @return Twig_Source
     */
    public function getSourceContext()
    {
        return $this->template->getSourceContext();
    }
}
