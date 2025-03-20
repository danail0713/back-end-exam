<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* modules/custom/themes_block/templates/themes.html.twig */
class __TwigTemplate_ec2ed3df9cf6299d743f1e4e01ac4a57 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 1
        yield "<div class=\"themes\">
\t<h3>Themes</h3>
\t<ol class=\"themes-list\">
\t\t";
        // line 4
        if (($context["themes"] ?? null)) {
            // line 5
            yield "\t\t\t";
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["themes"] ?? null));
            $context['loop'] = [
              'parent' => $context['_parent'],
              'index0' => 0,
              'index'  => 1,
              'first'  => true,
            ];
            if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof \Countable)) {
                $length = count($context['_seq']);
                $context['loop']['revindex0'] = $length - 1;
                $context['loop']['revindex'] = $length;
                $context['loop']['length'] = $length;
                $context['loop']['last'] = 1 === $length;
            }
            foreach ($context['_seq'] as $context["_key"] => $context["theme"]) {
                // line 6
                yield "\t\t\t\t<li class=\"theme-item\">
\t\t\t\t\t<div class=\"theme-header\">
\t\t\t\t\t\t<span class=\"theme-number\">";
                // line 8
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["loop"], "index", [], "any", false, false, true, 8), "html", null, true);
                yield ".</span>
\t\t\t\t\t\t<span class=\"theme-title\">";
                // line 9
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "title", [], "any", false, false, true, 9), "html", null, true);
                yield "</span>
\t\t\t\t\t\t<button class=\"toggle-btn\" aria-expanded=\"false\" aria-controls=\"theme-content-";
                // line 10
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["loop"], "index", [], "any", false, false, true, 10), "html", null, true);
                yield "\">+</button>
\t\t\t\t\t</div>
\t\t\t\t\t<div class=\"theme-body\" id=\"theme-content-";
                // line 12
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["loop"], "index", [], "any", false, false, true, 12), "html", null, true);
                yield "\">
\t\t\t\t\t\t<p class=\"theme-description\">";
                // line 13
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "description", [], "any", false, false, true, 13));
                yield "</p>
\t\t\t\t\t\t<strong>Resources</strong>
\t\t\t\t\t\t";
                // line 15
                if (((CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "resources", [], "any", false, false, true, 15) && ($context["user_enrolled"] ?? null)) && (CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "accessResources", [], "any", false, false, true, 15) == true))) {
                    // line 16
                    yield "\t\t\t\t\t\t\t<ul class=\"theme-resources\">
\t\t\t\t\t\t\t\t";
                    // line 17
                    $context['_parent'] = $context;
                    $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "resources", [], "any", false, false, true, 17));
                    foreach ($context['_seq'] as $context["_key"] => $context["resource"]) {
                        // line 18
                        yield "\t\t\t\t\t\t\t\t\t<li>
\t\t\t\t\t\t\t\t\t\t<a href=\"";
                        // line 19
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["resource"], "fileUrl", [], "any", false, false, true, 19), "html", null, true);
                        yield "\">";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["resource"], "fileName", [], "any", false, false, true, 19), "html", null, true);
                        yield "</a>
\t\t\t\t\t\t\t\t\t</li>
\t\t\t\t\t\t\t\t";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_key'], $context['resource'], $context['_parent']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 22
                    yield "\t\t\t\t\t\t\t</ul>
              <br>
\t\t\t\t\t\t";
                } elseif ( !CoreExtension::getAttribute($this->env, $this->source,                 // line 24
$context["theme"], "resources", [], "any", false, false, true, 24)) {
                    // line 25
                    yield "\t\t\t\t\t\t\t<p>No resources available.</p>
\t\t\t\t\t\t";
                } elseif ( !                // line 26
($context["user_enrolled"] ?? null)) {
                    // line 27
                    yield "\t\t\t\t\t\t\t<p>You can't access resources because you aren't enrolled for this course.</p>
\t\t\t\t\t\t";
                } elseif ((                // line 28
($context["accessResources"] ?? null) == false)) {
                    // line 29
                    yield "\t\t\t\t\t\t\t<p>You can't access resources because you haven't made the homework from the previous theme.</p>
\t\t\t\t\t\t";
                }
                // line 31
                yield "            ";
                if ((($context["user_enrolled"] ?? null) && CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework", [], "any", false, false, true, 31))) {
                    // line 32
                    yield "              <strong>Homework</strong>
\t\t\t\t\t\t\t<p>";
                    // line 33
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework", [], "any", false, false, true, 33));
                    yield "</p>
            ";
                } elseif (( !CoreExtension::getAttribute($this->env, $this->source,                 // line 34
$context["theme"], "homework", [], "any", false, false, true, 34) && ($context["user_enrolled"] ?? null))) {
                    // line 35
                    yield "              <strong>Homework</strong>
\t\t\t\t\t\t\t<p>No homework yet.</p>
            ";
                }
                // line 38
                yield "\t\t\t\t\t</div>
\t\t\t\t</li>
\t\t\t";
                ++$context['loop']['index0'];
                ++$context['loop']['index'];
                $context['loop']['first'] = false;
                if (isset($context['loop']['revindex0'], $context['loop']['revindex'])) {
                    --$context['loop']['revindex0'];
                    --$context['loop']['revindex'];
                    $context['loop']['last'] = 0 === $context['loop']['revindex0'];
                }
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['theme'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 41
            yield "\t\t";
        } else {
            // line 42
            yield "\t\t\t<p>No themes found.</p>
\t\t";
        }
        // line 44
        yield "\t</ol>
</div>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["themes", "loop", "user_enrolled", "accessResources"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "modules/custom/themes_block/templates/themes.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  178 => 44,  174 => 42,  171 => 41,  155 => 38,  150 => 35,  148 => 34,  144 => 33,  141 => 32,  138 => 31,  134 => 29,  132 => 28,  129 => 27,  127 => 26,  124 => 25,  122 => 24,  118 => 22,  107 => 19,  104 => 18,  100 => 17,  97 => 16,  95 => 15,  90 => 13,  86 => 12,  81 => 10,  77 => 9,  73 => 8,  69 => 6,  51 => 5,  49 => 4,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/custom/themes_block/templates/themes.html.twig", "/workspaces/back-end-exam/web/modules/custom/themes_block/templates/themes.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["if" => 4, "for" => 5];
        static $filters = ["escape" => 8, "raw" => 13];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['if', 'for'],
                ['escape', 'raw'],
                [],
                $this->source
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
