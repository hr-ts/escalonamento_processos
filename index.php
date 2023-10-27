<?php
$gantt     = '';
$processos = [];
$cores     = ['indigo', 'teal', 'red', 'blue', 'yellow', 'orange', 'violet', 'gray'];
$count     = 0;
$inicio    = 0;
$quantum   = 0;
$circular  = [];
if (isset($_POST['algoritmo'])) {
    foreach (array_chunk($_POST, 2) as $processo) {
        $processos[$processo[0]] = $processo[1];
    }

    $quantum = $processos['circ'] ?? 0; // Se $processos['circ'] existir, quantum recebe seu valor, caso contrario recebe 0
    array_pop($processos); //remove ultimo elemento do array de processos, que no caso e o algoritmo

    switch ($_POST['algoritmo']) {
        case 'sjf':
            asort($processos); //organiza o array em ordem crescente, pelas chaves
            foreach ($processos as $processo => $tempo) {
                $gantt  .= "<tr class='leading-tight'><th class='px-2 w-32 border border-black py-0 my-0'>$processo</th>";
                $gantt  .= str_repeat("<td class='border border-black w-5 bg-neutral-100'></td>", $inicio);
                $gantt  .= str_repeat("<td class='border border-black w-5 bg-$cores[$count]-500'></td>", $tempo);
                $gantt  .= "<td class='font-sant font-medium'>$tempo</td></tr>";
                $count  = $count >= (sizeof($cores) - 1) ? 0 : $count += 1;
                $inicio += $tempo;
            }
            break;
        case 'fcfs':
            foreach ($processos as $processo => $tempo) {
                $gantt  .= "<tr class='leading-tight'><th class='px-2 w-32 border border-black'>$processo</th>";
                $gantt  .= str_repeat("<td class='border border-black w-5 bg-neutral-100'></td>", $inicio);
                $gantt  .= str_repeat("<td class='border border-black w-5 bg-$cores[$count]-500'></td>", $tempo);
                $gantt  .= "<td class='font-sant font-medium'>$tempo</td></tr>";
                $count  = $count >= (sizeof($cores) - 1) ? 0 : $count += 1;
                $inicio += $tempo;
            }
            break;

        case 'circ':
            $circular = array_map(function ($tempo) {
                return array_fill(0, $tempo, 1);
            }, $processos);

            $circular = array_map(function ($processos) use ($quantum) {
                return array_chunk($processos, $quantum);
            }, $circular);

            foreach ($circular as $processo => &$tempo) {
                $gantt .= "<tr class='leading-tight'><th class='px-2 w-32 border border-black'>$processo</th>";

                while (count(end($circular))) {
                    if (count($circular) === 0) {
                        break;
                    }
                    $gantt .= str_repeat("<td class='border border-black w-5 bg-neutral-100'></td>", $inicio);
                    $gantt .= str_repeat("<td class='border border-black w-5 bg-$cores[$count]-500'></td>", count($tempo));
                    array_splice($tempo, 0, 1);
                    $gantt  .= "</tr>";
                    $inicio += count($tempo);
                    $count  = $count >= (sizeof($cores) - 1) ? 0 : $count += 1;
                }
            }
            break;
    }
}
?>

<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Escalonamento</title>

    <link rel="stylesheet" href="./css/output.css">
</head>
<body class="bg-gray-100 grid h-screen">
<div class="bg-white lg:w-4/5 lg:h-3/4 sm:h-6/6 sm:w-10/12 m-auto shadow-2xl rounded-2xl p-4">
    <p class="text-center font-sans text-xl font-medium">Escalonamento de processos</p>
    <div class="py-5">
        <label class="block tracking-tight" for="numero_processos">Nº Processos</label>
        <input id="numero_processos" value="<?= $_POST['n_processos'] ?? '' ?>" type="number" class="w-1/12 px-1 py-0.5 rounded border border-gray-200 focus:outline-none focus:border-1 focus:border-blue-500"
               name="n_processos" required>
        <button id="gera_processos" type="button" class="px-2 py-1 bg-indigo-500 rounded text-white font-medium font-sans tracking-tight focus:ring focus:ring-blue-700">Gerar</button>
    </div>
    <form id="lista_processos" method="POST" class="max-w-max hidden">
        <div id="processos" class="max-h-52 overflow-y-auto border rounded-lg p-2 grid gap-2">

        </div>
        <div id="options" class="hidden py-5">
            <label class="block tracking-tight" for="algoritmo">Tipo de Algoritmo</label>
            <select id="algoritmo" type="number" class="w-3/12 px-1 py-0.5 rounded border border-gray-200 focus:outline-none focus:border-1 focus:border-blue-500" name="algoritmo">
                <option value="sjf">SJF</option>
                <option value="fcfs">FCFS</option>
                <option value="circ">Circular</option>
            </select>
        </div>

        <button id="gera_grafico" type="submit" class="mb-5 px-2 py-1 bg-indigo-500 rounded text-white font-medium font-sans tracking-tight focus:ring focus:ring-blue-700">Gerar</button>
    </form>
    <div class="max-w-full overflow-auto">
        <table class="border-collapse">
            <tbody>
            <?= $gantt ?>
            </tbody>
        </table>
    </div>
</div>
</body>
<script>
    document.getElementById('gera_processos').addEventListener('click', () => {
        if (document.getElementById('numero_processos').value !== '') {
            gera_tabela_de_processos(document.getElementById('numero_processos').value);
        }
    });

    document.getElementById('algoritmo').addEventListener('change', function (e) {
        if (e.target.value === 'circ') {
            document.getElementById('options').insertAdjacentHTML('beforeend', `<input id="quantum" type="number" class="w-3/12 px-1 py-0.5 rounded border border-gray-200 focus:outline-none focus:border-1 focus:border-blue-500" name="quantum" placeholder="Quantum" required>`)
        } else document.getElementById('quantum').remove();
    });

    function gera_tabela_de_processos(qtd) {
        let html = '';
        for (let i = 0; i < parseInt(qtd); i++) {
            html += `<div class="flex gap-2">
                        <input name="nome_${i}" placeholder="Nome" class="w-3/12 px-1 py-0.5 rounded border border-gray-200 focus:outline-none focus:border-1 focus:border-blue-500" type="text" required>
                        <input name="tempo_${i}" placeholder="Tempo" class="w-3/12 px-1 py-0.5 rounded border border-gray-200 focus:outline-none focus:border-1 focus:border-blue-500" type="text" required>
                        <p class="font-sans tracking-tight my-auto text-sm">Processo ${i} </p>
                    </div>`;
        }

        document.getElementById('processos').innerHTML = html;
        document.getElementById('lista_processos').classList.remove('hidden');
        document.getElementById('options').classList.remove('hidden');
    }
</script>
</html>
