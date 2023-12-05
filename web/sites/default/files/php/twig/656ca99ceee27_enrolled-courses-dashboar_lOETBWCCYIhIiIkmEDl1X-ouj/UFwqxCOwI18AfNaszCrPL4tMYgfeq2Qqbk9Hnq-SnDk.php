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

/* modules/custom/student_enrollment/templates/enrolled-courses-dashboard.html.twig */
class __TwigTemplate_b4dc48b92905d58c5b2113c80f09c86d extends Template
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
        // line 2
        echo "
<h2>My enrolled courses</h2>
";
        // line 4
        if (($context["courses"] ?? null)) {
            // line 5
            echo "\t";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["courses"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["course"]) {
                // line 6
                echo "\t\t\t<h3>";
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, $context["course"], "label", [], "any", false, false, true, 6), 6, $this->source), "html", null, true);
                echo "</h3>
\t";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['course'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
        } else {
            // line 9
            echo "\t<p>No enrolled courses found.</p>
";
        }
    }

    public function getTemplateName()
    {
        return "modules/custom/student_enrollment/templates/enrolled-courses-dashboard.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  60 => 9,  50 => 6,  45 => 5,  43 => 4,  39 => 2,);
    }

    public function getSourceContext()
    {
        return new Source("{# enrolled-courses-dashboard.html.twig #}

<h2>My enrolled courses</h2>
{% if courses %}
\t{% for course in courses %}
\t\t\t<h3>{{ course.label }}</h3>
\t{% endfor %}
{% else %}
\t<p>No enrolled courses found.</p>
{% endif %}
", "modules/custom/student_enrollment/templates/enrolled-courses-dashboard.html.twig", "/workspaces/back-end-exam/web/modules/custom/student_enrollment/templates/enrolled-courses-dashboard.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("if" => 4, "for" => 5);
        static $filters = array("escape" => 6);
        static $functions = array();

        try {
            $this->sandbox->checkSecurity(
                ['if', 'for'],
                ['escape'],
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
