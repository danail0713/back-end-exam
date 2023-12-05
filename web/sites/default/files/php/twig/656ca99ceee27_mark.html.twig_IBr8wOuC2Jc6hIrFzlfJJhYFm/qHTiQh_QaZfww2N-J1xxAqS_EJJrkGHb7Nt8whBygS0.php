<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* core/themes/claro/templates/classy/content/mark.html.twig */
class __TwigTemplate_4f79ebb4884acf813b2535ec82ff5c8f extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 14
        if (($context["logged_in"] ?? null)) {
            // line 15
            echo "  ";
            if ((($context["status"] ?? null) === constant("MARK_NEW"))) {
                // line 16
                echo "    <span class=\"marker\">";
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("New"));
                echo "</span>
  ";
            } elseif ((            // line 17
($context["status"] ?? null) === constant("MARK_UPDATED"))) {
                // line 18
                echo "    <span class=\"marker\">";
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Updated"));
                echo "</span>
  ";
            }
        }
    }

    public function getTemplateName()
    {
        return "core/themes/claro/templates/classy/content/mark.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  51 => 18,  49 => 17,  44 => 16,  41 => 15,  39 => 14,);
    }

    public function getSourceContext()
    {
        return new Source("{#
/**
 * @file
 * Theme override for a marker for new or updated content.
 *
 * Available variables:
 * - status: Number representing the marker status to display. Use the constants
 *   below for comparison:
 *   - MARK_NEW
 *   - MARK_UPDATED
 *   - MARK_READ
 */
#}
{% if logged_in %}
  {% if status is constant('MARK_NEW') %}
    <span class=\"marker\">{{ 'New'|t }}</span>
  {% elseif status is constant('MARK_UPDATED') %}
    <span class=\"marker\">{{ 'Updated'|t }}</span>
  {% endif %}
{% endif %}
", "core/themes/claro/templates/classy/content/mark.html.twig", "/workspaces/back-end-exam/web/core/themes/claro/templates/classy/content/mark.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("if" => 14);
        static $filters = array("t" => 16);
        static $functions = array();

        try {
            $this->sandbox->checkSecurity(
                ['if'],
                ['t'],
                []
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
