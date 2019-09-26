<?php

class Render
{
    private static $template;
    private static $data;
    private static $arrReplace = [
        '<?php',
        '<?=',
        ';?>',
        '?>',
        '<%',
        '%>'
    ];

    public static function view(Array $template, Array $data = array())
    {
        try {
            self::$template = implode(' ', $template);
            self::$data = $data;
            unset($template);
            unset($data);
            self::clean(self::$data);
            //var_dump(self::$data['paises']['EUA']['Distrito de Colúmbia']['testeLevel 2']);die;

            extract(self::$data, EXTR_PREFIX_INVALID, 'var');

            self::getYield();
            self::getStructure();
            self::shortEcho();
            self::declareTag();

            $result = eval('?>' . self::$template . '>');
            return $result;
        } catch (Exception $e) {
            throw new Exception('Erro: ' . $e->getMessage());
        } catch (Error $e) {
            echo '<b>Erro de sintaxe na view</b> :: ' . $e->getMessage() . ' :: <b>' . __METHOD__ . '</b>';
        }
    }

    /**
     * Remove tags php dos dados informados pela controller.
     * @var Array $arr recebe os dados enviados pela controller.
     */
    private static function clean(&$arr)
    {
        try {
            if (is_array($arr)) {
                array_walk($arr, function (&$value) {
                    return self::clean($value);
                });
            } elseif (is_string($arr)) {
                $arr = str_replace(self::$arrReplace, '', $arr);
            }
            return $arr;
        } catch (Exception $e) {
            throw new Exception('Erro: ' . $e->getMessage());
        } catch (Error $e) {
            echo '<b>Erro de sintaxe na view</b> :: ' . $e->getMessage() . ' :: <b>' . __METHOD__ . '</b>';
        }
    }

    /**
     * Substitui a syntax blade pela syntax php correspondente
     */
    private static function getStructure()
    {
        try {
            $arrTagsToReplaceClose = [
                '@foreach(',
                '@elseif(',
                '@else',
                '@while(',
                '@for(',
                '@if('
            ];

            $arrCloseTags = [
                '@endforeach',
                '@endwhile',
                '@endfor',
                '@endif'
            ];

            $tam = strlen(self::$template);
            $newTemplate = '';
            $replace = false;
            $j = 0;

            for ($i = 0; $i < ($tam - 1); $i++) {
                if (self::$template[$i] == '@') {
                    foreach ($arrTagsToReplaceClose as $tag) {
                        $aux = substr(self::$template, $i - 1, 15);
                        if (strpos($aux, $tag)) {
                            $replace = true;
                        }
                    }
                }

                if (self::$template[$i] == '(' && $replace == true) {
                    $j++;
                }

                if (self::$template[$i] == ')' && $j >= 1) {
                    $j--;
                    if ($j == 0 && $replace == true) {
                        $newTemplate .= '){ ?>';
                        $replace = false;
                        continue;
                    }
                }
                $newTemplate .= self::$template[$i];
            }

            self::$template = $newTemplate;

            foreach ($arrTagsToReplaceClose as $tag) {
                if (strpos(self::$template, $tag)) {
                    if ($tag == '@elseif(') {
                        $newTag = '<?php } elseif(';
                    } elseif ($tag == '@else') {
                        $newTag = '<?php } else { ?>';
                    } else {
                        $newTag = str_replace('@', '<?php ', $tag);
                    }
                    self::$template = str_replace($tag, $newTag, self::$template);
                }
            }

            foreach ($arrCloseTags as $tag) {
                if (strpos(self::$template, $tag)) {
                    $newTag = '<?php } ?>';
                    self::$template = str_replace($tag, $newTag, self::$template);
                }
            }

            self::clearDouble();
        } catch (Exception $e) {
            throw new Exception('Erro: ' . $e->getMessage());
        } catch (Error $e) {
            echo '<b>Erro de sintaxe na view</b> :: ' . $e->getMessage() . ' :: <b>' . __METHOD__ . '</b>';
        }
    }

    /**
     * Substitui variáveis e constantes pos seus respectivos valores
     */
    private static function shortEcho()
    {
        try {
            $arrValues = self::getFunctionParam("{{", "}}");
            foreach ($arrValues as $value) {
                $value = str_replace(self::$arrReplace, '', $value);
                $value = str_replace(['     ', '    ', '   ', '  '], ' ', $value);
                self::$template = str_replace(["{{ $value }}", "{{" . $value . "}}"], '<?= ' . $value . ' ;?>', self::$template);
            }
        } catch (Exception $e) {
            throw new Exception('Erro: ' . $e->getMessage());
        } catch (Error $e) {
            echo '<b>Erro de sintaxe na view</b> :: ' . $e->getMessage() . ' :: <b>' . __METHOD__ . '</b>';
        }
    }

    /**
     * Substitui variáveis e constantes pos seus respectivos valores
     */
    private static function declareTag()
    {
        try {
            $arrValues = self::getFunctionParam("{!!", "!!}");
            foreach ($arrValues as $value) {
                $value = str_replace(self::$arrReplace, '', $value);
                $value = str_replace(['     ', '    ', '   ', '  '], ' ', $value);
                self::$template = str_replace(["{!! " . $value . " !!}", "{!!" . $value . "!!}"], '<?php ' . $value . ' ;?>', self::$template);
            }
        } catch (Exception $e) {
            throw new Exception('Erro: ' . $e->getMessage());
        } catch (Error $e) {
            echo '<b>Erro de sintaxe na view</b> :: ' . $e->getMessage() . ' :: <b>' . __METHOD__ . '</b>';
        }
    }

    /**
     * Inclui templates views ao template principal de forma recursiva,
     * se no template incluido houver a chamada de outro template 
     * ele também sera incluido recursivamente
     */
    private static function getYield()
    {
        try {
            $arrYields = self::getFunctionParam("@yield('", "')");
            foreach ($arrYields as $yield) {
                $tpl = file_get_contents($yield);
                self::$template = str_replace("@yield('{$yield}')", $tpl, self::$template);
                if (strpos(self::$template, '@yield')) {
                    self::getYield();
                }
            }
        } catch (Exception $e) {
            throw new Exception('Erro: ' . $e->getMessage());
        } catch (Error $e) {
            echo '<b>Erro de sintaxe na view</b> :: ' . $e->getMessage() . ' :: <b>' . __METHOD__ . '</b>';
        }
    }

    /**
     * Obtem o valor passado dentro dos blocos de comando view
     * @var String $param Conjunto de caracteres que serão substituidos pela tag de abertura de php ( '<?php' ou '<?=' )
     * @var String $complement Conjunto de caracteres que serão substituidos pela tag de fechamento de php ( '?>' )
     * @return Array $values Coleção de parametros recebidos da view.
     */
    private static function getFunctionParam(String $param, String $complement)
    {
        try {
            $arrParams = explode($param, self::$template);
            $values = [];

            if (is_array($arrParams) && count($arrParams) > 2) {
                for ($i = 1; $i < count($arrParams); $i++) {
                    $arrAux = explode($complement, $arrParams[$i]);
                    $values[] = $arrAux[0];
                }
            } elseif (is_array($arrParams) && count($arrParams) == 2) {
                $arrAux = explode($complement, $arrParams[1]);
                $values[] = $arrAux[0];
            }

            return $values;
        } catch (Exception $e) {
            throw new Exception('Erro: ' . $e->getMessage());
        } catch (Error $e) {
            echo '<b>Erro de sintaxe na view</b> :: ' . $e->getMessage() . ' :: <b>' . __METHOD__ . '</b>';
        }
    }

    /**
     * Remove tags duplicadas de fechamento de php
     */
    private static function clearDouble()
    {
        try {
            if (strpos(self::$template, '){ ?>{ ?>')) {
                self::$template = str_replace('){ ?>{ ?>', '){ ?>', self::$template);
            }
        } catch (Exception $e) {
            throw new Exception('Erro: ' . $e->getMessage());
        } catch (Error $e) {
            echo '<b>Erro de sintaxe na view</b> :: ' . $e->getMessage() . ' :: <b>' . __METHOD__ . '</b>';
        }
    }
}
