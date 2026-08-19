<?php
require '../lib/settings.inc.php';

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action      = $_POST['action'];

switch ($action) {
  case 'load-tabla_1':
    echo json_encode($_POST); ?>
    <a class="btn btn-danger btn-delete" data-title="¿Borrar carpeta?" data-message="¿Realmente desea borrar la carpeta de acceso?" data-row="<?= htmlspecialchars('{
    "glossary": {
        "title": "example glossary",
		"GlossDiv": {
            "title": "S",
			"GlossList": {
                "GlossEntry": {
                    "ID": "SGML",
					"SortAs": "SGML",
					"GlossTerm": "Standard Generalized Markup Language",
					"Acronym": "SGML",
					"Abbrev": "ISO 8879:1986",
					"GlossDef": {
                        "para": "A meta-markup language, used to create markup languages such as DocBook.",
						"GlossSeeAlso": ["GML", "XML"]
                    },
					"GlossSee": "markup"
                }
            }
        }
    }
}'); ?>" href="javascript:void(0)">
      borrar
    </a>
<?php
    echo paginate($_POST['page'], 10, 4);
    die;
    break;

  case 'load-tabla_2':
    echo json_encode($_POST);
    echo paginate($_POST['page'], 10, 4);
    die;
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;
