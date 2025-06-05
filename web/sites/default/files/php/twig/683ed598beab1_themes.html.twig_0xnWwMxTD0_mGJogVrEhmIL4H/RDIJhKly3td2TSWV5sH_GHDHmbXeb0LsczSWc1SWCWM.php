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
\t<h3>";
        // line 2
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Themes"));
        yield "</h3>
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

\t\t\t\t\t\t<strong>";
                // line 15
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Resources"));
                yield "</strong>
\t\t\t\t\t\t";
                // line 16
                if (( !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "resources", [], "any", false, false, true, 16)) && ((($context["user_enrolled"] ?? null) && (CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "accessResources", [], "any", false, false, true, 16) == true)) || ($context["user_course_instructor"] ?? null)))) {
                    // line 17
                    yield "\t\t\t\t\t\t\t<ul class=\"theme-resources\">
\t\t\t\t\t\t\t\t";
                    // line 18
                    $context['_parent'] = $context;
                    $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "resources", [], "any", false, false, true, 18));
                    foreach ($context['_seq'] as $context["_key"] => $context["resource"]) {
                        // line 19
                        yield "\t\t\t\t\t\t\t\t\t<li>
\t\t\t\t\t\t\t\t\t\t<a href=\"";
                        // line 20
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["resource"], "url", [], "any", false, false, true, 20), "html", null, true);
                        yield "\">";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["resource"], "name", [], "any", false, false, true, 20), "html", null, true);
                        yield "</a>
\t\t\t\t\t\t\t\t\t</li>
\t\t\t\t\t\t\t\t";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_key'], $context['resource'], $context['_parent']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 23
                    yield "\t\t\t\t\t\t\t</ul>
\t\t\t\t\t\t\t<br>
\t\t\t\t\t\t";
                } elseif (Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source,                 // line 25
$context["theme"], "resources", [], "any", false, false, true, 25))) {
                    // line 26
                    yield "\t\t\t\t\t\t\t<p>";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("No resources available."));
                    yield "</p>
\t\t\t\t\t\t";
                } elseif ( !                // line 27
($context["user_enrolled"] ?? null)) {
                    // line 28
                    yield "\t\t\t\t\t\t\t<p>";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("You don't have access to the resources."));
                    yield "</p>
\t\t\t\t\t\t";
                } elseif ((                // line 29
($context["accessResources"] ?? null) == false)) {
                    // line 30
                    yield "\t\t\t\t\t\t\t<p>";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("You can't access the resources because you haven't made the homework from the previous theme."));
                    yield "</p>
\t\t\t\t\t\t";
                }
                // line 32
                yield "
\t\t\t\t\t\t";
                // line 33
                if ((Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework_response", [], "any", false, false, true, 33)) && (CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "accessResources", [], "any", false, false, true, 33) == true))) {
                    // line 34
                    yield "\t\t\t\t\t\t\t";
                    if (((($context["user_enrolled"] ?? null) && (CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework", [], "any", false, false, true, 34) != "no homeworks accepted")) && (CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework", [], "any", false, false, true, 34) != ""))) {
                        // line 35
                        yield "\t\t\t\t\t\t\t\t<strong>";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Homework"));
                        yield "</strong>
\t\t\t\t\t\t\t\t";
                        // line 36
                        if ( !CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "submitted_homework", [], "any", false, false, true, 36)) {
                            // line 37
                            yield "\t\t\t\t\t\t\t\t\t<p>";
                            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework", [], "any", false, false, true, 37));
                            yield "</p>
\t\t\t\t\t\t\t\t\t";
                            // line 38
                            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["homework_form"] ?? null), "html", null, true);
                            yield "
\t\t\t\t\t\t\t\t";
                        } else {
                            // line 40
                            yield "\t\t\t\t\t\t\t\t\t<p>";
                            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("You've already sent your homework for check. Waiting for grade."));
                            yield "</p>
\t\t\t\t\t\t\t\t";
                        }
                        // line 42
                        yield "\t\t\t\t\t\t\t";
                    } elseif (((CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework", [], "any", false, false, true, 42) == "no homeworks accepted") && ($context["user_enrolled"] ?? null))) {
                        // line 43
                        yield "\t\t\t\t\t\t\t\t<strong>";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Homework"));
                        yield "</strong>
\t\t\t\t\t\t\t\t<p>";
                        // line 44
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("The time for sending homeworks has expired."));
                        yield "</p>
\t\t\t\t\t\t\t";
                    } elseif (( !CoreExtension::getAttribute($this->env, $this->source,                     // line 45
$context["theme"], "homework", [], "any", false, false, true, 45) && ($context["user_enrolled"] ?? null))) {
                        // line 46
                        yield "\t\t\t\t\t\t\t\t<strong>";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Homework"));
                        yield "</strong>
\t\t\t\t\t\t\t\t<p>";
                        // line 47
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("No homework yet."));
                        yield "</p>
\t\t\t\t\t\t\t";
                    }
                    // line 49
                    yield "\t\t\t\t\t\t";
                } elseif (( !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework_response", [], "any", false, false, true, 49)) && (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework_response", [], "any", false, false, true, 49), "grade", [], "any", false, false, true, 49) < 4.5))) {
                    // line 50
                    yield "\t\t\t\t\t\t\t<h4>";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Homework grade:"));
                    yield " ";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework_response", [], "any", false, false, true, 50), "grade", [], "any", false, false, true, 50), "html", null, true);
                    yield "</h4>
\t\t\t\t\t\t\t<h4>";
                    // line 51
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Homework comment:"));
                    yield "</h4>
\t\t\t\t\t\t\t";
                    // line 52
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework_response", [], "any", false, false, true, 52), "comment", [], "any", false, false, true, 52), "html", null, true);
                    yield "
\t\t\t\t\t\t\t";
                    // line 53
                    if ((CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework", [], "any", false, false, true, 53) != "no homeworks accepted")) {
                        // line 54
                        yield "\t\t\t\t\t\t\t\t<h5>";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Send homework again for a higher grade."));
                        yield "</h5>
\t\t\t\t\t\t\t\t";
                        // line 55
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["homework_form"] ?? null), "html", null, true);
                        yield "
\t\t\t\t\t\t\t";
                    } else {
                        // line 57
                        yield "\t\t\t\t\t\t\t\t<p>";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("The time for sending homeworks has expired."));
                        yield "</p>
\t\t\t\t\t\t\t";
                    }
                    // line 59
                    yield "\t\t\t\t\t\t</div>
\t\t\t\t\t";
                } elseif (( !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source,                 // line 60
$context["theme"], "homework_response", [], "any", false, false, true, 60)) && (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework_response", [], "any", false, false, true, 60), "grade", [], "any", false, false, true, 60) >= 4.5))) {
                    // line 61
                    yield "\t\t\t\t\t\t<h4>";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Homework grade:"));
                    yield " ";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework_response", [], "any", false, false, true, 61), "grade", [], "any", false, false, true, 61), "html", null, true);
                    yield "</h4>
\t\t\t\t\t\t<h4>";
                    // line 62
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Homework comment:"));
                    yield "</h4>
\t\t\t\t\t\t";
                    // line 63
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework_response", [], "any", false, false, true, 63), "comment", [], "any", false, false, true, 63), "html", null, true);
                    yield "
\t\t\t\t\t\t<h5>";
                    // line 64
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("You can access the resources from the next lection."));
                    yield "</h5>
\t\t\t\t\t";
                }
                // line 66
                yield "\t\t\t\t</li>
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
            // line 68
            yield "\t\t";
        } else {
            // line 69
            yield "\t\t\t<p>";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("No themes found."));
            yield "</p>
\t\t";
        }
        // line 71
        yield "\t</ol>
</div>

<style>
\t.homework-submit-btn {
\t\tfont-size: 1rem;
\t\tpadding: 4px 12px;
\t}
</style>

";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["themes", "loop", "user_enrolled", "user_course_instructor", "accessResources", "homework_form"]);        yield from [];
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
        return array (  291 => 71,  285 => 69,  282 => 68,  267 => 66,  262 => 64,  258 => 63,  254 => 62,  247 => 61,  245 => 60,  242 => 59,  236 => 57,  231 => 55,  226 => 54,  224 => 53,  220 => 52,  216 => 51,  209 => 50,  206 => 49,  201 => 47,  196 => 46,  194 => 45,  190 => 44,  185 => 43,  182 => 42,  176 => 40,  171 => 38,  166 => 37,  164 => 36,  159 => 35,  156 => 34,  154 => 33,  151 => 32,  145 => 30,  143 => 29,  138 => 28,  136 => 27,  131 => 26,  129 => 25,  125 => 23,  114 => 20,  111 => 19,  107 => 18,  104 => 17,  102 => 16,  98 => 15,  93 => 13,  89 => 12,  84 => 10,  80 => 9,  76 => 8,  72 => 6,  54 => 5,  52 => 4,  47 => 2,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/custom/themes_block/templates/themes.html.twig", "/workspaces/back-end-exam/web/modules/custom/themes_block/templates/themes.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["if" => 4, "for" => 5];
        static $filters = ["t" => 2, "escape" => 8, "raw" => 13];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['if', 'for'],
                ['t', 'escape', 'raw'],
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
