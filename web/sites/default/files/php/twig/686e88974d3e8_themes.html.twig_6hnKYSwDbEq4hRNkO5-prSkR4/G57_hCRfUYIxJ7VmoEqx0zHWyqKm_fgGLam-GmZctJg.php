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
class __TwigTemplate_cd378eee594b9cbb621a5290bc890f1f extends Template
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
\t\t\t\t\t\t<span class=\"theme-title\">
\t\t\t\t\t\t\t";
                // line 10
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "title", [], "any", false, false, true, 10), "html", null, true);
                yield "
\t\t\t\t\t\t\t";
                // line 11
                if ((CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "type", [], "any", true, true, true, 11) && CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "type", [], "any", false, false, true, 11))) {
                    // line 12
                    yield "\t\t\t\t\t\t\t\t-
\t\t\t\t\t\t\t\t";
                    // line 13
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t(Twig\Extension\CoreExtension::capitalize($this->env->getCharset(), CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "type", [], "any", false, false, true, 13))));
                    yield "
\t\t\t\t\t\t\t";
                }
                // line 15
                yield "\t\t\t\t\t\t</span>

\t\t\t\t\t\t<button class=\"toggle-btn\" aria-expanded=\"false\" aria-controls=\"theme-content-";
                // line 17
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["loop"], "index", [], "any", false, false, true, 17), "html", null, true);
                yield "\">+</button>
\t\t\t\t\t</div>
\t\t\t\t\t<div class=\"theme-body\" id=\"theme-content-";
                // line 19
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["loop"], "index", [], "any", false, false, true, 19), "html", null, true);
                yield "\">
\t\t\t\t\t\t<p class=\"theme-description\">";
                // line 20
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "description", [], "any", false, false, true, 20));
                yield "</p>

\t\t\t\t\t\t<strong>";
                // line 22
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Resources"));
                yield "</strong>
\t\t\t\t\t\t";
                // line 23
                if (( !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "resources", [], "any", false, false, true, 23)) && ((($context["user_enrolled"] ?? null) && (CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "accessResources", [], "any", false, false, true, 23) == true)) || ($context["user_course_instructor"] ?? null)))) {
                    // line 24
                    yield "\t\t\t\t\t\t\t<ul class=\"theme-resources\">
\t\t\t\t\t\t\t\t";
                    // line 25
                    $context['_parent'] = $context;
                    $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "resources", [], "any", false, false, true, 25));
                    foreach ($context['_seq'] as $context["_key"] => $context["resource"]) {
                        // line 26
                        yield "\t\t\t\t\t\t\t\t\t<li>
\t\t\t\t\t\t\t\t\t\t<a href=\"";
                        // line 27
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["resource"], "url", [], "any", false, false, true, 27), "html", null, true);
                        yield "\">";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["resource"], "name", [], "any", false, false, true, 27), "html", null, true);
                        yield "</a>
\t\t\t\t\t\t\t\t\t</li>
\t\t\t\t\t\t\t\t";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_key'], $context['resource'], $context['_parent']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 30
                    yield "\t\t\t\t\t\t\t</ul>
\t\t\t\t\t\t\t<br>
\t\t\t\t\t\t";
                } elseif (Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source,                 // line 32
$context["theme"], "resources", [], "any", false, false, true, 32))) {
                    // line 33
                    yield "\t\t\t\t\t\t\t<p>";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("No resources available."));
                    yield "</p>
\t\t\t\t\t\t";
                } elseif ( !                // line 34
($context["user_enrolled"] ?? null)) {
                    // line 35
                    yield "\t\t\t\t\t\t\t<p>";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("You don't have access to the resources."));
                    yield "</p>
\t\t\t\t\t\t";
                } elseif ((                // line 36
($context["accessResources"] ?? null) == false)) {
                    // line 37
                    yield "\t\t\t\t\t\t\t<p>";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("You can't access the resources because you haven't made the homework from the previous theme."));
                    yield "</p>
\t\t\t\t\t\t";
                }
                // line 39
                yield "
\t\t\t\t\t\t";
                // line 40
                if ((Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework_response", [], "any", false, false, true, 40)) && (CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "accessResources", [], "any", false, false, true, 40) == true))) {
                    // line 41
                    yield "\t\t\t\t\t\t\t";
                    if (((($context["user_enrolled"] ?? null) && (CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework", [], "any", false, false, true, 41) != "no homeworks accepted")) && (CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework", [], "any", false, false, true, 41) != ""))) {
                        // line 42
                        yield "\t\t\t\t\t\t\t\t<strong>";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Homework"));
                        yield "</strong>
\t\t\t\t\t\t\t\t";
                        // line 43
                        if ( !CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "submitted_homework", [], "any", false, false, true, 43)) {
                            // line 44
                            yield "\t\t\t\t\t\t\t\t\t<p>";
                            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework", [], "any", false, false, true, 44));
                            yield "</p>
\t\t\t\t\t\t\t\t\t";
                            // line 45
                            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["homework_form"] ?? null), "html", null, true);
                            yield "
\t\t\t\t\t\t\t\t";
                        } else {
                            // line 47
                            yield "\t\t\t\t\t\t\t\t\t<p>";
                            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("You've already sent your homework for check. Waiting for grade."));
                            yield "</p>
\t\t\t\t\t\t\t\t";
                        }
                        // line 49
                        yield "\t\t\t\t\t\t\t";
                    } elseif (((CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework", [], "any", false, false, true, 49) == "no homeworks accepted") && ($context["user_enrolled"] ?? null))) {
                        // line 50
                        yield "\t\t\t\t\t\t\t\t<strong>";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Homework"));
                        yield "</strong>
\t\t\t\t\t\t\t\t<p>";
                        // line 51
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("The time for sending homeworks has expired."));
                        yield "</p>
\t\t\t\t\t\t\t";
                    } elseif (( !CoreExtension::getAttribute($this->env, $this->source,                     // line 52
$context["theme"], "homework", [], "any", false, false, true, 52) && ($context["user_enrolled"] ?? null))) {
                        // line 53
                        yield "\t\t\t\t\t\t\t\t<strong>";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Homework"));
                        yield "</strong>
\t\t\t\t\t\t\t\t<p>";
                        // line 54
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("No homework yet."));
                        yield "</p>
\t\t\t\t\t\t\t";
                    }
                    // line 56
                    yield "\t\t\t\t\t\t";
                } elseif (( !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework_response", [], "any", false, false, true, 56)) && (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework_response", [], "any", false, false, true, 56), "grade", [], "any", false, false, true, 56) < 4.5))) {
                    // line 57
                    yield "\t\t\t\t\t\t\t<h4>";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Homework grade:"));
                    yield "
\t\t\t\t\t\t\t\t";
                    // line 58
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework_response", [], "any", false, false, true, 58), "grade", [], "any", false, false, true, 58), "html", null, true);
                    yield "
\t\t\t\t\t\t\t\t<h4>";
                    // line 59
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Homework comment:"));
                    yield "</h4>
\t\t\t\t\t\t\t\t";
                    // line 60
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework_response", [], "any", false, false, true, 60), "comment", [], "any", false, false, true, 60), "html", null, true);
                    yield "
\t\t\t\t\t\t\t\t";
                    // line 61
                    if ((CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework", [], "any", false, false, true, 61) != "no homeworks accepted")) {
                        // line 62
                        yield "\t\t\t\t\t\t\t\t\t<h5>";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Send homework again for a higher grade."));
                        yield "</h5>
\t\t\t\t\t\t\t\t\t";
                        // line 63
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["homework_form"] ?? null), "html", null, true);
                        yield "
\t\t\t\t\t\t\t\t";
                    } else {
                        // line 65
                        yield "\t\t\t\t\t\t\t\t\t<p>";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("The time for sending homeworks has expired."));
                        yield "</p>
\t\t\t\t\t\t\t\t";
                    }
                    // line 67
                    yield "\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t";
                } elseif (( !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source,                 // line 68
$context["theme"], "homework_response", [], "any", false, false, true, 68)) && (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework_response", [], "any", false, false, true, 68), "grade", [], "any", false, false, true, 68) >= 4.5))) {
                    // line 69
                    yield "\t\t\t\t\t\t\t<h4>";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Homework grade:"));
                    yield "
\t\t\t\t\t\t\t\t";
                    // line 70
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework_response", [], "any", false, false, true, 70), "grade", [], "any", false, false, true, 70), "html", null, true);
                    yield "</h4>
\t\t\t\t\t\t\t<h4>";
                    // line 71
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Homework comment:"));
                    yield "</h4>
\t\t\t\t\t\t\t";
                    // line 72
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework_response", [], "any", false, false, true, 72), "comment", [], "any", false, false, true, 72), "html", null, true);
                    yield "
\t\t\t\t\t\t\t";
                    // line 73
                    if ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "homework_response", [], "any", false, false, true, 73), "grade", [], "any", false, false, true, 73) == 6.0)) {
                        // line 74
                        yield "\t\t\t\t\t\t\t\t<h5>";
                        yield "You can access the resources from the next lection";
                        yield "</h5>
\t\t\t\t\t\t\t";
                    } else {
                        // line 76
                        yield "\t\t\t\t\t\t\t\t<h5>";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("You can access the resources from the next lection but you can still send your homework for a higher grade."));
                        yield "</h5>
                ";
                        // line 77
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["homework_form"] ?? null), "html", null, true);
                        yield "
\t\t\t\t\t\t\t";
                    }
                    // line 79
                    yield "\t\t\t\t\t\t";
                }
                // line 80
                yield "\t\t\t\t\t\t";
                if (($context["user_enrolled"] ?? null)) {
                    // line 81
                    yield "\t\t\t\t\t\t\t<a href=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->getPath("student_enrollment.ask_question", ["theme_id" => CoreExtension::getAttribute($this->env, $this->source, $context["theme"], "id", [], "any", false, false, true, 81), "instructor_id" => ($context["instructor_id"] ?? null)]), "html", null, true);
                    yield "\" class=\"button button--secondary homework-submit-btn\">
\t\t\t\t\t\t\t\t";
                    // line 82
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Ask a question"));
                    yield "
\t\t\t\t\t\t\t</a>
\t\t\t\t\t\t";
                }
                // line 85
                yield "\t\t\t\t\t</li>
\t\t\t\t";
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
            // line 87
            yield "\t\t\t";
        } else {
            // line 88
            yield "\t\t\t\t<p>";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("No themes found."));
            yield "</p>
\t\t\t";
        }
        // line 90
        yield "\t\t</ol>
\t</div>


\t<style>
\t\t.homework-submit-btn {
\t\t\tfont-size: 1rem;
\t\t\tpadding: 4px 12px;
\t\t}
\t</style>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["themes", "loop", "user_enrolled", "user_course_instructor", "accessResources", "homework_form", "instructor_id"]);        yield from [];
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
        return array (  340 => 90,  334 => 88,  331 => 87,  316 => 85,  310 => 82,  305 => 81,  302 => 80,  299 => 79,  294 => 77,  289 => 76,  283 => 74,  281 => 73,  277 => 72,  273 => 71,  269 => 70,  264 => 69,  262 => 68,  259 => 67,  253 => 65,  248 => 63,  243 => 62,  241 => 61,  237 => 60,  233 => 59,  229 => 58,  224 => 57,  221 => 56,  216 => 54,  211 => 53,  209 => 52,  205 => 51,  200 => 50,  197 => 49,  191 => 47,  186 => 45,  181 => 44,  179 => 43,  174 => 42,  171 => 41,  169 => 40,  166 => 39,  160 => 37,  158 => 36,  153 => 35,  151 => 34,  146 => 33,  144 => 32,  140 => 30,  129 => 27,  126 => 26,  122 => 25,  119 => 24,  117 => 23,  113 => 22,  108 => 20,  104 => 19,  99 => 17,  95 => 15,  90 => 13,  87 => 12,  85 => 11,  81 => 10,  76 => 8,  72 => 6,  54 => 5,  52 => 4,  47 => 2,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/custom/themes_block/templates/themes.html.twig", "/workspaces/back-end-exam/web/modules/custom/themes_block/templates/themes.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["if" => 4, "for" => 5];
        static $filters = ["t" => 2, "escape" => 8, "capitalize" => 13, "raw" => 20];
        static $functions = ["path" => 81];

        try {
            $this->sandbox->checkSecurity(
                ['if', 'for'],
                ['t', 'escape', 'capitalize', 'raw'],
                ['path'],
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
