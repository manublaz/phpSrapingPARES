<?php 

include("config.php"); // Conexión a la base de datos
include("func.functions.php"); // Funciones generales
$totalLinks="375000"; // Máximo del contador
$maxAttempts = 3; // Número de intentos máximos
$retryDelay = 2; // Segundos para esperar antes de reintentar

for ($kkk=270000; $kkk<$totalLinks; $kkk++) {
    
    
    // CÓDIGO CURL PARA EXTRAER CÓDIGO HTML _________________________________>>>>>>>>>>>>>>>>>
    // ======================================================================>>>>>>>>>>>>>>>>>
    
    
    // URL de la página web a rastrear
    $urlAA = "https://pares.mcu.es/ParesBusquedas20/catalogo/autoridad/$kkk";
    
    echo "<span title='$kkk'>+ </span> ";
    
    // DETECTAR DUPLICACIONES >>>>>
    $resultadoDUP = $con->query("SELECT COUNT(*) AS count FROM autoridades WHERE enlace = '$urlAA';");
    if ($resultadoDUP) {
        $filaDUP = $resultadoDUP->fetch_assoc(); $nDUP = $filaDUP['count'];
        if ($nDUP > 0) { } else {
    
            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            
                // Inicializa cURL
                $ch = curl_init($urlAA);
                
                // Configura cURL para devolver el HTML
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                
                // Realiza la solicitud HTTP
                $html = curl_exec($ch);
                
                // Cierra la conexión cURL
                curl_close($ch);
                
                // DESCARGA HTML CORRECTA
                if ($html !== false && !empty($html)) {
                
                    // Crea un nuevo objeto DOM
                    @$dom = new DOMDocument();
                    
                    // Carga el HTML obtenido de la página
                    @$dom->loadHTML($html);
                    
                    // Crea un objeto XPath para realizar consultas
                    $xpath = new DOMXPath($dom);
            
                    
                    // En caso de Enlace incorrecto pasa al siguiente ^^^^^^^^^^^^^^
                    if (strpos($html, 'id="error_mensaje"') !== false || strpos($html, "id='error_mensaje'") !== false) {
                    } else {
                
                        
                        // INICIAR VARIABLES DE INDEXACIÓN ______________________________________>>>>>>>>>>>>>>>>>
                        // ======================================================================>>>>>>>>>>>>>>>>>
                        
                        $indexer = "";
                        $indexerLiteral = "";
                        
                        
                        
                        // EXTRACCIÓN DE CONTENIDOS _____________________________________________>>>>>>>>>>>>>>>>>
                        // ======================================================================>>>>>>>>>>>>>>>>>
                        
                        
                        // Extraer información de Identificación
                        $identificacion = [];
                        $identificacionNodes = $xpath->query('//h3[.="Identificación"]/following-sibling::div[@class="info"]');
                        foreach ($identificacionNodes as $node) {
                            $titulo = $xpath->query('h4[@class="aviso"]', $node)[0]->textContent;
                            $contenido = $xpath->query('p', $node)[0]->textContent;
                            $identificacion[$titulo] = $contenido;
                        }
                        
                        
                        // Extraer "Término preferente" y "NO preferente"
                        $terminoPreferenteNode = $xpath->query('//h4[contains(., "Término preferente:")]/following-sibling::p');
                        
                        
                        // Extraer información de "Fechas de existencia"
                        $fechasExistenciaNode = $xpath->query('//h4[contains(., "Fechas de existencia:")]/following-sibling::p|//h4[contains(., "Fechas de Existencia:")]/following-sibling::p');
                        
                        
                        // Extraer información de Historia
                        $historiaNode = $xpath->query('//div[@id="historiaNotas"]');
                        
                        
                        // Extraer las coordenadas "Latitud" y "Longitud"
                        $coordenadasNode = $xpath->query('//h4[contains(.,"Coordenadas:")]/following-sibling::p');
                        
                        
                        // Extraer información de "Lugar de Nacimiento", "Lugar de Defunción", 
                        // "Lugar de Residencia", "Lugar general", "Lugares relacionados"
                        $lugarNacimiento = $xpath->query('//h4[contains(., "Lugar de Nacimiento:")]/following-sibling::p');
                        $lugarDefuncion = $xpath->query('//h4[contains(., "Lugar de Defunción:")]/following-sibling::p');
                        $lugarResidencia = $xpath->query('//h4[contains(., "Lugar de Residencia:")]/following-sibling::p');
                        $lugarGeneral = $xpath->query('//h3[.="Lugares"]/following-sibling::div/p');
                        $lugaresRelacionadosNodes = $xpath->query('//h4[contains(.,"Términos Genéricos:")]/following-sibling::placerelation');
                        
                        
                        // Extraer información de Conceptos/Objetos/Acontecimientos
                        $conceptos = [];
                        $conceptosNode = $xpath->query('//h3[.="Conceptos/Objetos/Acontecimientos"]/following-sibling::div[@class="info"]');
                        if ($conceptosNode->length > 0) {
                            for ($i = 0; $i < $conceptosNode->length; $i++) {
                                $enlaces = $xpath->query('.//a', $conceptosNode[$i]);
                                foreach ($enlaces as $enlace) {
                                    $texto = $enlace->textContent;
                                    $url = $enlace->getAttribute('href');
                                    $conceptos[] = [
                                        'texto' => $texto,
                                        'url' => $url,
                                    ];
                                }
                            }
                        } 
                        
                        
                        
                        // Extraer "Normas/Atribuciones Legales"
                        $normasAtribucionesLegales = [];
                        $h3NormasAtribucionesLegales = $xpath->query('//h3[.="Normas/Atribuciones Legales"]/following-sibling::div/descriptivenote/p');
                        if ($h3NormasAtribucionesLegales->length > 0) {
                            // Iterar a través de los párrafos encontrados
                            foreach ($h3NormasAtribucionesLegales as $parrafo) {
                                $texto = $parrafo->textContent;
                                $normasAtribucionesLegales[] = $texto;
                            }
                        }
                        
                        
                        // Extraer información de "(Función) Desempeña/lleva a cabo/realiza"
                        $ocupacionesNode = $xpath->query('//h3[.="Ocupaciones"]/following-sibling::div[@class="info"]');
                        
                        
                        // Extraer información de Fuentes - Con bucle
                        $fuentes = [];
                        $fuentesNode = $xpath->query('//h3[.="Fuentes"]/following-sibling::div');
                        if ($fuentesNode->length > 0) {
                            foreach ($fuentesNode as $fuenteNode) {
                                // Si el nodo es un enlace (<a>), procesa como enlace
                                if ($fuenteNode->nodeName === 'a') {
                                    $texto = $fuenteNode->textContent;
                                    $url = $fuenteNode->getAttribute('href');
                                    $fuentes[] = [
                                        'texto' => $texto,
                                        'url' => $url,
                                    ];
                                } else {
                                    // Si el nodo no es un enlace, es un párrafo (<p>), accede al texto con nodeValue
                                    $texto = $fuenteNode->nodeValue;
                                    $fuentes[] = [
                                        'texto' => $texto,
                                        'url' => '', // No hay URL en este caso
                                    ];
                                }
                            }
                        }
                        
                        
                        // Extraer "Relaciones familiares" y "Relaciones asociativas"
                        $relacionesFamiliares = $xpath->query('//h4[contains(., "Relaciones familiares")]/following-sibling::p|//h4[contains(., "Relaciones Familiares")]/following-sibling::p');
                        $h4RelacionesAsociativas = $xpath->query('//h4[contains(., "asociativas")]/following-sibling::p|//h4[contains(., "asociativas")]/preceding-sibling::p|//h4[contains(., "Asociativa")]/following-sibling::p');
                        
                        
                        // Extraer "Funciones Relacionadas"
                        $funcionesRelacionadas = $xpath->query('//frelation//a');
                        
                        
                        // Extraer información de Enlaces Externos - Con bucle
                        $enlacesExternos = [];
                        $enlacesExternosNode = $xpath->query('//h3[.="Enlaces Externos"]/following-sibling::div[@class="info"]|//h3[.="Enlaces externos"]/following-sibling::div[@class="info"]');
                        if ($enlacesExternosNode->length > 0) {
                            foreach ($enlacesExternosNode as $enlaceExternoNode) {
                                $enlaces = $xpath->query('.//a', $enlaceExternoNode);
                                foreach ($enlaces as $enlace) {
                                    $texto = $enlace->textContent;
                                    $url = $enlace->getAttribute('href');
                                    $enlacesExternos[] = [
                                        'texto' => $texto,
                                        'url' => $url,
                                    ];
                                }
                            }
                        }
                        
                        // Extraer información de "Documentos relacionados" - Con bucle
                        $documentosRelacionados = [];
                        $documentosRelacionadosNodes = $xpath->query('//h3[.="Documentos"]/following-sibling::div[@class="info"]/ul/li');  
                        foreach ($documentosRelacionadosNodes as $documentoNode) {
                            $texto = $documentoNode->textContent;
                            $url = $xpath->evaluate('string(.//a/@href)', $documentoNode);
                            $documentosRelacionados[] = [
                                'texto' => $texto,
                                'url' => $url,
                            ];
                        }
                        
                        // Extraer términos específicos
                        $terminosEspecificos = [];
                        $terminosEspecificosNode = $xpath->query('//h4[.="Términos Específicos:"]/following-sibling::ul/subjectrelation/li');
                        foreach ($terminosEspecificosNode as $terminoEspecificoNode) {
                            $termino = $xpath->evaluate('string(.//h41)', $terminoEspecificoNode);
                            $enlace = $xpath->evaluate('string(.//a/@href)', $terminoEspecificoNode);
                            $enlaceSemantico = $xpath->evaluate('string(../@xmlns)', $terminoEspecificoNode);
                            $terminosEspecificos[] = [
                                'termino' => $termino,
                                'enlace' => $enlace,
                                'enlace_semantico' => $enlaceSemantico,
                            ];
                        }
                        
                        
                        // REPRESENTACIÓN DE RESULTADOS _________________________________________>>>>>>>>>>>>>>>>>
                        // ======================================================================>>>>>>>>>>>>>>>>>
                        
                        
                        // Imprimir el valor de "Tipo"
                        $formType="";
                        // echo "<h3>Tipo</h3>" . $identificacion['Tipo:'] . "<br/>";
                        $formType = $identificacion['Tipo:'];
                        /* Index */ $indexer .= $identificacion['Tipo:']." "; $indexerLiteral .= $identificacion['Tipo:']." ";
                        
                        // Imprimir "Forma autorizada"
                        // echo "<h3>Forma autorizada</h3>"; 
                        $formName="";
                        if (isset($identificacion['Forma autorizada:'])) {
                            $formName = $identificacion['Forma autorizada:'];
                            // echo "<li>$formName</li>";
                            /* Index */ $indexer .= $identificacion['Forma autorizada:']." "; $indexerLiteral .= $identificacion['Forma autorizada:']." ";
                        } else {
                            // echo "<li>No se ha encontrado forma autorizada del nombre</li>";
                        }
                        
                        
                        // Imprimir "Término preferente" 
                        // echo "<h3>Término preferente</h3>"; 
                        $prefTerm=""; 
                        $prefNotTerm="";
                        if (!empty($terminoPreferenteNode)) {
                            $pNode = $terminoPreferenteNode->item(0);
                            if($pNode=="" || empty($pNode)){  } else {
                                $textoTerminoPreferente = $pNode->textContent;
                                // echo "<li><b>Preferente</b> $textoTerminoPreferente</li>";
                                /* DATA_ */ $prefTerm .= "$textoTerminoPreferente#@#";
                                /* Index */ $indexer = "$textoTerminoPreferente"; $indexerLiteral = "$textoTerminoPreferente";
                                $enlaceTerminoNoPreferente = $xpath->query('.//a[text()="Término no preferente"]', $pNode)->item(0);
                                if ($enlaceTerminoNoPreferente) {
                                    $tituloEnlace = $enlaceTerminoNoPreferente->getAttribute('title');
                                    $titulosArray = explode("\n", $tituloEnlace);
                                    $titulosArray = array_filter($titulosArray, 'strlen');
                                    // echo "<ul>";
                                    foreach ($titulosArray as $titulo) {
                                        // echo "<li><b>No preferente</b> $titulo</li>";
                                        /* DATA_ */ $prefNotTerm .= "$titulo#@#";
                                        /* Index */ $indexer .= "$titulo "; $indexerLiteral .= "$titulo ";
                                    }
                                    // echo "</ul>";
                                } else {
                                    // echo "<li>No se encontró el enlace Término no preferente</li>";
                                }
                            }
                        } else {
                            // echo "<li>No se encontró el término preferente.</li>";
                        }
                        
                        
                        // Imprimir "Fechas de existencia"
                        // echo "<h3>Fechas de existencia</h3>"; 
                        $fechasExistencia="";
                        if ($fechasExistenciaNode->length > 0) {
                            $fechasExistencia = trim($fechasExistenciaNode[0]->textContent);
                            // echo "<li><b>Fechas</b> " . $fechasExistencia . "</li>";
                            /* Index */ $indexer .= "$fechasExistencia "; $indexerLiteral .= "$fechasExistencia ";
                        } else {
                            // echo "<li>Fechas de existencia no encontradas</li>";
                        }
                        
                        // Imprimir "Lugar de nacimiento"
                        // echo "<h3>Lugares</h3>"; 
                        $dataLugNac="";
                        if ($lugarNacimiento->length > 0) {
                            $lugarNacimientoTexto = trim($lugarNacimiento[0]->textContent);
                            // Extraer el enlace del lugar de nacimiento
                            $lugarNacimientoEnlace = $xpath->evaluate('string(.//a/@href)', $lugarNacimiento[0]);
                            // echo "<li><b>Lugar Nacimiento</b> $lugarNacimientoTexto - $lugarNacimientoEnlace</li>";
                            /* DATA_ */ $dataLugNac .= "$lugarNacimientoTexto|$lugarNacimientoEnlace#@#";
                            /* Index */ $indexer .= "$lugarNacimientoTexto "; $indexerLiteral .= "$lugarNacimientoTexto ";
                        } else {
                            // echo "<li>Lugar de Nacimiento no encontrado</li>";
                        }
                        
                        // Imprimir "Lugar de defunción"
                        $dataLugDef="";
                        if ($lugarDefuncion->length > 0) {
                            $lugarDefuncionTexto = trim($lugarDefuncion[0]->textContent);
                            // Extraer el enlace del lugar de defunción
                            $lugarDefuncionEnlace = $xpath->evaluate('string(.//a/@href)', $lugarDefuncion[0]);
                            // echo "<li><b>Lugar Defunción</b> $lugarDefuncionTexto - $lugarDefuncionEnlace</li>";
                            /* DATA_ */ $dataLugDef .= "$lugarDefuncionTexto|$lugarDefuncionEnlace#@#";
                            /* Index */ $indexer .= "$lugarDefuncionTexto "; $indexerLiteral .= "$lugarDefuncionTexto ";
                        } else {
                            // echo "<li>Lugar de Defunción no encontrado</li>";
                        }
                        
                        // Imprimir "Lugar de residencia"
                        $dataLugRes="";
                        if ($lugarResidencia->length > 0) {
                            $lugarResidenciaTexto = trim($lugarResidencia[0]->textContent);
                            // Extraer el enlace del lugar de defunción
                            $lugarResidenciaEnlace = $xpath->evaluate('string(.//a/@href)', $lugarResidencia[0]);
                            // echo "<li><b>Lugar Residencia</b> $lugarResidenciaTexto - $lugarResidenciaEnlace</li>";
                            /* DATA_ */ $dataLugRes .= "$lugarResidenciaTexto|$lugarResidenciaEnlace#@#";
                            /* Index */ $indexer .= "$lugarResidenciaTexto "; $indexerLiteral .= "$lugarResidenciaTexto ";
                        } else {
                            // echo "<li>Lugar de Residencia no encontrado</li>";
                        }
                        
                        // Imprimir "Lugar general"
                        $dataLugGen="";
                        if ($lugarGeneral->length > 0) {
                            $lugarGeneralTexto = trim($lugarGeneral[0]->textContent);
                            // Extraer el enlace del lugar de defunción
                            $lugarGeneralEnlace = $xpath->evaluate('string(.//a/@href)', $lugarGeneral[0]);
                            // echo "<li><b>Lugar General</b> $lugarGeneralTexto - $lugarGeneralEnlace</li>";
                            /* DATA_ */ $dataLugGen .= "$lugarGeneralTexto|$lugarGeneralEnlace#@#";
                            /* Index */ $indexer .= "$lugarGeneralTexto "; $indexerLiteral .= "$lugarGeneralTexto ";
                        } else {
                            // echo "<li>Lugar General no encontrado</li>";
                        }
                        
                        
                        
                        // Imprimir "Lugares relacionados"
                        // echo "<h3>Lugares Relacionados</h3>"; 
                        $dataLugRel="";
                        foreach ($lugaresRelacionadosNodes as $lugarNode) {
                            $pNodes = $lugarNode->getElementsByTagName('p');
                            foreach ($pNodes as $pNode) {
                                // Obtén el texto y el enlace dentro del nodo p
                                $aNode = $xpath->query('.//a', $pNode)->item(0); // Obtén el primer enlace dentro de p
                                if ($aNode !== null) {
                                    $texto = $aNode->textContent;
                                    $url = $aNode->getAttribute('href');
                                    // echo "<li><b>Texto</b> $texto</li>";
                                    // echo "<li><b>Enlace</b> $url</li>";
                                    /* DATA_ */ $dataLugRel .= "$texto|$url#@#";
                                    /* Index */ $indexer .= "$texto "; $indexerLiteral .= "$texto ";
                                } else {}
                            }
                        }
                        
                        
                        // Imprimir Coordenadas "Latitud" y "Longitud"
                        // echo "<h3>Coordenadas</h3>"; 
                        $latitud=""; 
                        $longitud="";
                        if ($coordenadasNode->length > 0) {
                            $coordenadasTexto = $coordenadasNode[0]->textContent;
                            $partes = preg_split('/Latitud:|Longitud:/', $coordenadasTexto, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
                            if (count($partes) >= 2) {
                                $latitud = trim($partes[1]); $latitud = preg_replace("/Ver en mapa/", "", $latitud); $latitud = trim($latitud); // Latitud
                                $longitud = trim($partes[2]); $longitud = preg_replace("/Ver en mapa/", "", $longitud); $longitud = trim($longitud); // Longitud
                                // echo "<li><b>Latitud</b> $latitud</li>";
                                // echo "<li><b>Longitud</b> $longitud</li>";
                            } else {
                                // echo "<li>No se encontraron coordenadas válidas</li>";
                            }
                        } else {
                            // echo "<li>No se encontraron coordenadas</li>";
                        }
                        
                        
                        // Imprimir la sección de "Historia"
                        // echo "<h3>Historia</h3>"; 
                        $dataHist="";
                        if ($historiaNode->length > 0) { 
                            $historiaText = $dom->saveHTML($historiaNode[0]); // Reemplazar las etiquetas <p> por un espacio de separación
                            $historiaText = preg_replace('/<\/p>/', ' ', $historiaText); // Eliminar todas las demás etiquetas HTML
                            $historiaText = strip_tags($historiaText);
                            // echo "<li>" . $historiaText . "</li>";
                            /* DATA_ */ $dataHist .= "$historiaText#@#";
                            /* Index */ $indexer .= "$historiaText "; $indexerLiteral .= "$historiaText ";
                        }
                        
                        
                        // Imprimir los enlaces y el texto de "Conceptos/Objetos/Acontecimientos"
                        // echo "<h3>Enlaces de Conceptos/Objetos/Acontecimientos</h3>"; 
                        $dataConcept="";
                        foreach ($conceptos as $concepto) {
                            // echo "<li><b>Texto</b> " . $concepto['texto'] . "</li>";
                            // echo "<li><b>URL</b> " . $concepto['url'] . "</li>";
                            // echo "<br/>";
                            /* DATA_ */ $dataConcept .= "$concepto[texto]|$concepto[url]#@#";
                            /* Index */ $indexer .= $concepto['texto']." "; $indexerLiteral .= $concepto['texto']." ";
                        }
                        
                        
                        // Imprime los párrafos encontrados "Normas/Atribuciones Legales"
                        // echo "<h3>Normas/Atribuciones Legales</h3>"; 
                        $dataLegal="";
                        foreach ($normasAtribucionesLegales as $parrafo) {
                            // echo "<li>" . $parrafo . "</li>";
                            /* DATA_ */ $dataLegal .= "$parrafo#@#";
                            /* Index */ $indexer .= "$parrafo "; $indexerLiteral .= "$parrafo ";
                        }
                        
                        
                        // Imprimir información de las Ocupaciones
                        // echo "<h3>Ocupaciones</h3>"; 
                        $dataFunc="";
                        if ($ocupacionesNode->length > 0) {
                            // Extraer la ocupación y su enlace
                            foreach ($ocupacionesNode as $ocupacionNode) {
                                $ocupacionEnlace = $xpath->evaluate('string(.//h41/a/@href)', $ocupacionNode);
                                $ocupacionTexto = $xpath->evaluate('string(.//h41/a/text())', $ocupacionNode);
                                // echo "<li><b>Enlace</b> " . $ocupacionEnlace . "</li>";
                                // echo "<li><b>Texto</b> " . $ocupacionTexto . "</li>";
                                /* DATA_ */ $dataFunc .= "$ocupacionTexto|$ocupacionEnlace#@#";
                                /* Index */ $indexer .= "$ocupacionTexto "; $indexerLiteral .= "$ocupacionTexto ";
                            }
                            
                            // Extraer la función
                            $funcionNode = $xpath->query('//h4[contains(text(), "Función")]/following-sibling::p', $ocupacionesNode[0]); 
                            if ($funcionNode->length > 0) {
                                $funcionTexto = trim($funcionNode[0]->textContent);
                                // echo "<li><b>Función</b> " . $funcionTexto . "</li>";
                                /* Index */ $indexer .= "$funcionTexto "; $indexerLiteral .= "$funcionTexto ";
                                
                                $enlaces = $xpath->query('.//h41/a', $funcionNode[0]);
                                foreach ($enlaces as $enlace) {
                                    $enlaceTexto = $enlace->textContent;
                                    $enlaceURL = $enlace->getAttribute('href');
                                    // echo "<li>Enlace - " . $enlaceURL . "</li>";
                                    // echo "<li>Texto del enlace - " . $enlaceTexto . "</li>";
                                    /* DATA_ */ $dataFunc .= "$enlaceTexto|$enlaceURL#@#";
                                    /* Index */ $indexer .= "$enlaceTexto "; $indexerLiteral .= "$enlaceTexto ";
                                }
                                
                            } else {
                                // echo "<li>No se encontró información de función</li>";
                            }
                        } else {
                            // echo "<li>No se encontró información de ocupaciones</li>";
                        }
                        
                        
                        // Imprimir "Funciones relacionadas"
                        // echo "<h3>Funciones relacionadas</h3>"; 
                        $dataFuncRel="";
                        if ($funcionesRelacionadas->length > 0) {
                            foreach ($funcionesRelacionadas as $enlace) {
                                $texto = $enlace->nodeValue; // Extraer el texto del enlace
                                $url = $enlace->getAttribute('href'); // Extraer la URL del enlace
                                // echo "<li><b>Texto</b> $texto</li>";
                                // echo "<li><b>URL</b> $url</li>";
                                /* DATA_ */ $dataFuncRel .= "$texto|$url#@#";
                                /* Index */ $indexer .= "$texto "; $indexerLiteral .= "$texto ";
                            }
                        } else {
                            // echo "<li>No se encontraron funciones relacionadas</li>";
                        }
                        
                        
                        // Imprimir "Términos específicos"
                        // echo "<h3>Término Específico</h3>"; 
                        $dataTE="";
                        foreach ($terminosEspecificos as $terminoEspecifico) {
                            // echo "<p><b>Término:</b> " . $terminoEspecifico['termino'] . "</p>";
                            if (!empty($terminoEspecifico['enlace'])) {
                                // echo "<p><b>Enlace:</b> " . $terminoEspecifico['enlace'] . "</p>";
                            }
                            // echo "<p><b>Enlace Semántico:</b> " . $terminoEspecifico['enlace_semantico'] . "</p>";
                            // echo "<hr>"; // Separador entre términos específicos
                            /* DATA_ */ $dataTE .= "$terminoEspecifico[termino]|$terminoEspecifico[enlace]|$terminoEspecifico[enlace_semantico]#@#";
                            /* Index */ $indexer .= $terminoEspecifico['termino']." "; $indexerLiteral .= $terminoEspecifico['termino']." ";
                        }
                        
                        
                        // Imprimir las "fuentes" y sus enlaces
                        // echo "<h3>Fuentes</h3>"; 
                        $dataFuentes="";
                        foreach ($fuentes as $fuente) { 
                            // echo "<li><b>Texto</b> " . $fuente['texto'] . "</li>";
                            // echo "<li><b>URL</b> " . $fuente['url'] . "</li>";
                            // echo "<br/>";
                            /* DATA_ */ $dataFuentes .= "$fuente[texto]|$fuente[url]#@#";
                            /* Index */ $indexer .= $fuente['texto']." "; $indexerLiteral .= $fuente['texto']." ";
                        }
                        
                        // Imprimir "Relaciones familiares"
                        // echo "<h3>Relaciones familiares</h3>"; 
                        $dataFamiRel="";
                        if ($relacionesFamiliares->length > 0) {
                            // Iterar a través de las relaciones familiares
                            foreach ($relacionesFamiliares as $relacionFamiliar) {
                                $enlace = $xpath->evaluate('string(.//a/@href)', $relacionFamiliar);
                                $textoEnlace = trim($relacionFamiliar->textContent);
                                // Extraer el texto suplementario que indica el tipo de relación
                                $tipoRelacion = preg_match('/\-\s*(.*?)$/', $textoEnlace, $matches) ? $matches[1] : '';
                                // echo "<li><b>Enlace</b> " . $enlace . "</li>";
                                // echo "<li><b>Texto del enlace</b> " . $textoEnlace . "</li>";
                                // echo "<li><b>Tipo de relación</b> " . $tipoRelacion . "</li>";
                                /* DATA_ */ $dataFamiRel .= "$textoEnlace|$enlace|$tipoRelacion#@#";
                                /* Index */ $indexer .= "$textoEnlace "; $indexerLiteral .= "$textoEnlace ";
                            }
                        } else {
                            // echo "<li>Relaciones familiares no encontradas</li>";
                        }
                        
                        // Imprimir "Relaciones asociativas"
                        // echo "<h3>Relaciones asociativas</h3>"; 
                        $dataAsocRel="";
                        if ($h4RelacionesAsociativas->length > 0) {
                            foreach ($h4RelacionesAsociativas as $pRelacionNode) {
                                $enlaceRelacion = $xpath->evaluate('string(.//a/@href)', $pRelacionNode);
                                $textoRelacion = trim($pRelacionNode->textContent);
                                // echo "<li><b>Enlace</b> " . $enlaceRelacion . "</li>";
                                // echo "<li><b>Texto</b> " . $textoRelacion . "</li>";
                                /* DATA_ */ $dataAsocRel .= "$textoRelacion|$enlaceRelacion#@#";
                                /* Index */ $indexer .= "$textoRelacion "; $indexerLiteral .= "$textoRelacion ";
                            }
                        } else {
                            // echo "<li>No se encontraron relaciones asociativas</li>";
                        }
                        
                        // Imprimir los enlaces externos y sus enlaces
                        // echo "<h3>Enlaces externos</h3>"; 
                        $dataExtLink="";
                        foreach ($enlacesExternos as $enlacesExterno) {
                            // echo "<li><b>Texto</b> " . $enlacesExterno['texto'] . "</li>";
                            // echo "<li><b>URL</b> " . $enlacesExterno['url'] . "</li>";
                            // echo "<br/>";
                            /* DATA_ */ $dataExtLink .= "$enlacesExterno[texto]|$enlacesExterno[url]#@#";
                            /* Index */ $indexer .= $enlacesExterno['texto']." "; $indexerLiteral .= $enlacesExterno['texto']." ";
                        }
                        
                        // Imprimir la información de "Documentos relacionados"
                        // echo "<h3>Documentos relacionados</h3>"; 
                        $dataDocRel="";
                        foreach ($documentosRelacionados as $documento) {
                            // echo "<li><b>Texto</b> " .  $documento['texto'] . "</li>";
                            // echo "<li><b>URL</b> " .  $documento['url'] . "</li>";
                            // echo "<br/>";
                            /* DATA_ */ $dataDocRel .= "$documento[texto]|$documento[url]#@#";
                            /* Index */ $indexer .= $documento['texto']." "; $indexerLiteral .= $documento['texto']." ";
                        }
                        
                        
                        // PREPARAR INDEXACIÓN PARA BUSCADOR ____________________________________>>>>>>>>>>>>>>>>>
                        // ======================================================================>>>>>>>>>>>>>>>>>
                        $indexer=indexText($indexer);
                        $indexer .= "";
                        $indexerLiteral .= "";
                        echo "<div style='font-size:8px; margin-bottom: 30px; line-height:20px;'>$kkk - $indexerLiteral <a href='$urlAA' target='_blank'>$urlAA</a></div>";
                        
                        
                        // ELIMINAR COMILLAS DE VARIABLES DE TEXTO ______________________________>>>>>>>>>>>>>>>>>
                        // ======================================================================>>>>>>>>>>>>>>>>>
                        
                        $formName = deleteQuotes($formName);
                        $prefTerm = deleteQuotes($prefTerm);
                        $prefNotTerm = deleteQuotes($prefNotTerm);
                        $fechasExistencia = deleteQuotes($fechasExistencia);
                        $dataLugNac = deleteQuotes($dataLugNac);
                        $dataLugDef = deleteQuotes($dataLugDef);
                        $dataLugRes = deleteQuotes($dataLugRes);
                        $dataLugGen = deleteQuotes($dataLugGen);
                        $dataLugRel = deleteQuotes($dataLugRel);
                        $dataHist = deleteQuotes($dataHist);
                        $dataConcept = deleteQuotes($dataConcept);
                        $dataLegal = deleteQuotes($dataLegal);
                        $dataFunc = deleteQuotes($dataFunc);
                        $dataFuncRel = deleteQuotes($dataFuncRel);
                        $dataTE = deleteQuotes($dataTE);
                        $dataFuentes = deleteQuotes($dataFuentes);
                        $dataFamiRel = deleteQuotes($dataFamiRel);
                        $dataAsocRel = deleteQuotes($dataAsocRel);
                        $dataExtLink = deleteQuotes($dataExtLink);
                        $dataDocRel = deleteQuotes($dataDocRel);
                        $indexer = deleteQuotes($indexer);
                        $indexerLiteral = deleteQuotes($indexerLiteral);
                        
                        
                        
                        // INSERTAR DATOS EN BD "autoridades" ___________________________________>>>>>>>>>>>>>>>>>
                        // ======================================================================>>>>>>>>>>>>>>>>>
                        
                        // Consulta SQL para la inserción
                        $sql = "INSERT INTO autoridades SET 
                                tipo='$formType', 
                                enlace='$urlAA',
                                formaAutorizada='$formName', 
                                terminoPreferente='$prefTerm', 
                                terminoNoPreferente='$prefNotTerm', 
                                fechasExistencia='$fechasExistencia', 
                                lugarNacimiento='$dataLugNac', 
                                lugarDefuncion='$dataLugDef', 
                                lugarResidencia='$dataLugRes', 
                                lugarGeneral='$dataLugGen', 
                                lugaresRelacionados='$dataLugRel', 
                                latitud='$latitud', 
                                longitud='$longitud', 
                                historia='$dataHist', 
                                conceptosObjetos='$dataConcept', 
                                atribucionesLegales='$dataLegal', 
                                ocupaciones='$dataFunc', 
                                funcionesRelacionadas='$dataFuncRel', 
                                terminosEspecificos='$dataTE', 
                                fuentes='$dataFuentes', 
                                relacionesFamiliares='$dataFamiRel', 
                                relacionesAsociativas='$dataAsocRel', 
                                enlacesExternos='$dataExtLink', 
                                documentosRelacionados='$dataDocRel', 
                                indexer='$indexer', 
                                indexerLiteral='$indexerLiteral';";
                        
                        /*
                        echo "INSERT INTO autoridades SET 
                                tipo='$formType', 
                                enlace='$urlAA',
                                formaAutorizada='$formName', 
                                terminoPreferente='$prefTerm', 
                                terminoNoPreferente='$prefNotTerm', 
                                fechasExistencia='$fechasExistencia', 
                                lugarNacimiento='$dataLugNac', 
                                lugarDefuncion='$dataLugDef', 
                                lugarResidencia='$dataLugRes', 
                                lugarGeneral='$dataLugGen', 
                                lugaresRelacionados='$dataLugRel', 
                                latitud='$latitud', 
                                longitud='$longitud', 
                                historia='$dataHist', 
                                conceptosObjetos='$dataConcept', 
                                atribucionesLegales='$dataLegal', 
                                ocupaciones='$dataFunc', 
                                funcionesRelacionadas='$dataFuncRel', 
                                terminosEspecificos='$dataTE', 
                                fuentes='$dataFuentes', 
                                relacionesFamiliares='$dataFamiRel', 
                                relacionesAsociativas='$dataAsocRel', 
                                enlacesExternos='$dataExtLink', 
                                documentosRelacionados='$dataDocRel', 
                                indexer='$indexer', 
                                indexerLiteral='$indexerLiteral';";
                        */
                        
                        // Ejecuta la consulta de inserción
                        if ($con->query($sql) === TRUE) {
                            // echo "<li>Registro insertado correctamente</li>";
                        } else {
                            echo "<li>Error al insertar el registro " . $con->error; echo "</li>";
                        }
                        
                        unset(
                            $identificacionNodes,
                            $identificacion,
                            $terminoPreferenteNode,
                            $fechasExistenciaNode,
                            $historiaNode,
                            $coordenadasNode,
                            $lugarNacimiento,
                            $lugarDefuncion,
                            $lugarResidencia,
                            $lugarGeneral,
                            $lugaresRelacionadosNodes,
                            $conceptosNode,
                            $conceptos,
                            $enlaces,
                            $normasAtribucionesLegales,
                            $h3NormasAtribucionesLegales,
                            $ocupacionesNode,
                            $fuentes,
                            $fuentesNode,
                            $relacionesFamiliares,
                            $h4RelacionesAsociativas,
                            $funcionesRelacionadas,
                            $enlacesExternos,
                            $enlacesExternosNode,
                            $documentosRelacionados,
                            $documentosRelacionadosNodes,
                            $documentoNode,
                            $terminosEspecificos,
                            $terminosEspecificosNode,
                            $enlaceSemantico,
                            $indexer,
                            $indexerLiteral
                            );
                        
                        // usleep(4000000); // 4 segundo de pausa
                
                    }
                
                    break; // Sale del bucle si la solicitud y procesamiento son exitosos
                    
                } // SI LA DESCARGA DEL HTML FALLA...    
                
                if ($attempt < $maxAttempts) {
                    sleep($retryDelay); // Espera antes de volver a intentar
                }
                
            } // FIN BUCLE FOR DE INTENTOS DE DESCARGA
 
        }
    } // Fin comprobación Duplicados
} // FIN BUCLE PRINCIPAL - ITERACIONES

?>
