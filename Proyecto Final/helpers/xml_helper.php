<?php
/**
 * xml_helper.php — Capa intermediaria BD → XML
 * Convierte arrays de la BD en objetos SimpleXML para renderizar en las vistas.
 */

/**
 * Convierte un array de productos en un SimpleXMLElement.
 */
function productosToXML($productos) {
    $xml = new SimpleXMLElement('<productos/>');
    foreach ($productos as $prod) {
        $item = $xml->addChild('producto');
        $item->addChild('id', $prod['id']);
        $item->addChild('nombre', htmlspecialchars($prod['nombre']));
        $item->addChild('precio', $prod['precio']);
        $item->addChild('stock', $prod['stock']);
        // Si tiene BLOB, la URL apunta al controlador de imagen; si no, al archivo
        if (!empty($prod['imagen_blob'])) {
            $item->addChild('imagen_src', '../controllers/ImagenController.php?tipo=producto&id=' . $prod['id']);
        } else {
            $item->addChild('imagen_src', '../assets/img/' . htmlspecialchars($prod['imagen'] ?? ''));
        }
    }
    return $xml;
}

/**
 * Convierte un array de películas (con horarios concatenados) en un SimpleXMLElement.
 */
function peliculasToXML($peliculas) {
    $xml = new SimpleXMLElement('<peliculas/>');
    foreach ($peliculas as $peli) {
        $item = $xml->addChild('pelicula');
        $item->addChild('id', $peli['id']);
        $item->addChild('titulo', htmlspecialchars($peli['titulo']));
        $item->addChild('clasificacion', htmlspecialchars($peli['clasificacion']));
        // Imagen: BLOB o archivo
        if (!empty($peli['imagen_blob'])) {
            $item->addChild('imagen_src', '../controllers/ImagenController.php?tipo=pelicula&id=' . $peli['id']);
        } else {
            $item->addChild('imagen_src', '../assets/img/' . htmlspecialchars($peli['imagen'] ?? ''));
        }
        // Horarios (vienen como string separado por comas con formato id:hora:precio:sala)
        $horariosNode = $item->addChild('horarios');
        if (!empty($peli['horarios_data'])) {
            $partes = explode('|', $peli['horarios_data']);
            foreach ($partes as $parte) {
                $campos = explode(',', $parte);
                if (count($campos) >= 4) {
                    $h = $horariosNode->addChild('horario');
                    $h->addChild('id', $campos[0]);
                    $h->addChild('hora', $campos[1]);
                    $h->addChild('precio', $campos[2]);
                    $h->addChild('sala', htmlspecialchars($campos[3]));
                }
            }
        }
    }
    return $xml;
}
?>
