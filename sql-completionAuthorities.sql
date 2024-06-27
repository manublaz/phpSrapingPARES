WITH RankedRecords AS (
    SELECT
        id, formaAutorizada, enlace, tipo,

          -- Contabilizar la extensión en bytes
LENGTH(datepub) + LENGTH(dateupd) + LENGTH(tipo) + LENGTH(enlace) + LENGTH(formaAutorizada) + LENGTH(terminoPreferente) + LENGTH(terminoNoPreferente) + LENGTH(fechasExistencia) + LENGTH(lugarNacimiento) + LENGTH(lugarDefuncion) + LENGTH(lugarResidencia) + LENGTH(lugarGeneral) + LENGTH(lugaresRelacionados) + LENGTH(latitud) + LENGTH(longitud) + LENGTH(historia) + LENGTH(conceptosObjetos) + LENGTH(atribucionesLegales) + LENGTH(ocupaciones) + LENGTH(funcionesRelacionadas) + LENGTH(terminosEspecificos) + LENGTH(fuentes) + LENGTH(relacionesFamiliares) + LENGTH(relacionesAsociativas) + LENGTH(enlacesExternos) + LENGTH(documentosRelacionados) + LENGTH(indexer) + LENGTH(indexerLiteral) AS total_bytes,

-- Contabilizar el número de campos rellenados
(CASE WHEN datepub IS NOT NULL AND datepub != '' THEN 1 ELSE 0 END + 
 CASE WHEN dateupd IS NOT NULL AND dateupd != '' THEN 1 ELSE 0 END + 
 CASE WHEN tipo IS NOT NULL AND tipo != '' THEN 1 ELSE 0 END + 
 CASE WHEN enlace IS NOT NULL AND enlace != '' THEN 1 ELSE 0 END + 
 CASE WHEN formaAutorizada IS NOT NULL AND formaAutorizada != '' THEN 1 ELSE 0 END +   
 CASE WHEN terminoPreferente IS NOT NULL AND terminoPreferente != '' THEN 1 ELSE 0 END +  
 CASE WHEN terminoNoPreferente IS NOT NULL AND terminoNoPreferente != '' THEN 1 ELSE 0 END +  
 CASE WHEN fechasExistencia IS NOT NULL AND fechasExistencia != '' THEN 1 ELSE 0 END + 
 CASE WHEN lugarNacimiento IS NOT NULL AND lugarNacimiento != '' THEN 1 ELSE 0 END + 
 CASE WHEN lugarDefuncion IS NOT NULL AND lugarDefuncion != '' THEN 1 ELSE 0 END + 
 CASE WHEN lugarResidencia IS NOT NULL AND lugarResidencia != '' THEN 1 ELSE 0 END + 
 CASE WHEN lugarGeneral IS NOT NULL AND lugarGeneral != '' THEN 1 ELSE 0 END + 
 CASE WHEN lugaresRelacionados IS NOT NULL AND lugaresRelacionados != '' THEN 1 ELSE 0 END +  
 CASE WHEN latitud IS NOT NULL AND latitud != '' THEN 1 ELSE 0 END + 
 CASE WHEN longitud IS NOT NULL AND longitud != '' THEN 1 ELSE 0 END + 
 CASE WHEN historia IS NOT NULL AND historia != '' THEN 1 ELSE 0 END + 
 CASE WHEN conceptosObjetos IS NOT NULL AND conceptosObjetos != '' THEN 1 ELSE 0 END + 
 CASE WHEN atribucionesLegales IS NOT NULL AND atribucionesLegales != '' THEN 1 ELSE 0 END +   
 CASE WHEN ocupaciones IS NOT NULL AND ocupaciones != '' THEN 1 ELSE 0 END + 
 CASE WHEN funcionesRelacionadas IS NOT NULL AND funcionesRelacionadas != '' THEN 1 ELSE 0 END + 
 CASE WHEN terminosEspecificos IS NOT NULL AND terminosEspecificos != '' THEN 1 ELSE 0 END + 
 CASE WHEN fuentes IS NOT NULL AND fuentes != '' THEN 1 ELSE 0 END + 
 CASE WHEN relacionesFamiliares IS NOT NULL AND relacionesFamiliares != '' THEN 1 ELSE 0 END + 
 CASE WHEN relacionesAsociativas IS NOT NULL AND relacionesAsociativas != '' THEN 1 ELSE 0 END + 
 CASE WHEN enlacesExternos IS NOT NULL AND enlacesExternos != '' THEN 1 ELSE 0 END + 
 CASE WHEN documentosRelacionados IS NOT NULL AND documentosRelacionados != '' THEN 1 ELSE 0 END + 
 CASE WHEN indexer IS NOT NULL AND indexer != '' THEN 1 ELSE 0 END + 
 CASE WHEN indexerLiteral IS NOT NULL AND indexerLiteral != '' THEN 1 ELSE 0 END) 
 AS filled_fields,

-- Asignación de número de fila para la tabla temporal RankedRecords
ROW_NUMBER() OVER (PARTITION BY tipo ORDER BY 
(CASE WHEN datepub IS NOT NULL AND datepub != '' THEN 1 ELSE 0 END + 
 CASE WHEN dateupd IS NOT NULL AND dateupd != '' THEN 1 ELSE 0 END + 
 CASE WHEN tipo IS NOT NULL AND tipo != '' THEN 1 ELSE 0 END + 
 CASE WHEN enlace IS NOT NULL AND enlace != '' THEN 1 ELSE 0 END + 
 CASE WHEN formaAutorizada IS NOT NULL AND formaAutorizada != '' THEN 1 ELSE 0 END + 
 CASE WHEN terminoPreferente IS NOT NULL AND terminoPreferente != '' THEN 1 ELSE 0 END + 
 CASE WHEN terminoNoPreferente IS NOT NULL AND terminoNoPreferente != '' THEN 1 ELSE 0 END + 
 CASE WHEN fechasExistencia IS NOT NULL AND fechasExistencia != '' THEN 1 ELSE 0 END + 
 CASE WHEN lugarNacimiento IS NOT NULL AND lugarNacimiento != '' THEN 1 ELSE 0 END + 
 CASE WHEN lugarDefuncion IS NOT NULL AND lugarDefuncion != '' THEN 1 ELSE 0 END + 
 CASE WHEN lugarResidencia IS NOT NULL AND lugarResidencia != '' THEN 1 ELSE 0 END + 
 CASE WHEN lugarGeneral IS NOT NULL AND lugarGeneral != '' THEN 1 ELSE 0 END + 
 CASE WHEN lugaresRelacionados IS NOT NULL AND lugaresRelacionados != '' THEN 1 ELSE 0 END + 
 CASE WHEN latitud IS NOT NULL AND latitud != '' THEN 1 ELSE 0 END + 
 CASE WHEN longitud IS NOT NULL AND longitud != '' THEN 1 ELSE 0 END + 
 CASE WHEN historia IS NOT NULL AND historia != '' THEN 1 ELSE 0 END + 
 CASE WHEN conceptosObjetos IS NOT NULL AND conceptosObjetos != '' THEN 1 ELSE 0 END + 
 CASE WHEN atribucionesLegales IS NOT NULL AND atribucionesLegales != '' THEN 1 ELSE 0 END + 
 CASE WHEN ocupaciones IS NOT NULL AND ocupaciones != '' THEN 1 ELSE 0 END + 
 CASE WHEN funcionesRelacionadas IS NOT NULL AND funcionesRelacionadas != '' THEN 1 ELSE 0 END +   
 CASE WHEN terminosEspecificos IS NOT NULL AND terminosEspecificos != '' THEN 1 ELSE 0 END + 
 CASE WHEN fuentes IS NOT NULL AND fuentes != '' THEN 1 ELSE 0 END + 
 CASE WHEN relacionesFamiliares IS NOT NULL AND relacionesFamiliares != '' THEN 1 ELSE 0 END + 
 CASE WHEN relacionesAsociativas IS NOT NULL AND relacionesAsociativas != '' THEN 1 ELSE 0 END + 
 CASE WHEN enlacesExternos IS NOT NULL AND enlacesExternos != '' THEN 1 ELSE 0 END + 
 CASE WHEN documentosRelacionados IS NOT NULL AND documentosRelacionados != '' THEN 1 ELSE 0 END +
 CASE WHEN indexer IS NOT NULL AND indexer != '' THEN 1 ELSE 0 END + 
 CASE WHEN indexerLiteral IS NOT NULL AND indexerLiteral != '' THEN 1 ELSE 0 END) 
DESC, total_bytes DESC) AS rn
    FROM
        autoridades
    WHERE
        formaAutorizada != ""
)
SELECT
    id, formaAutorizada,  enlace, tipo, filled_fields, total_bytes
FROM
    RankedRecords
WHERE
    rn <= 5
ORDER BY
    tipo, filled_fields DESC, total_bytes DESC;