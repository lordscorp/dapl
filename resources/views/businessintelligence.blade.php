<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAPL - Dados Abertos de Processos de Licenciamento</title>
    <link rel="stylesheet" href="resources/css/bootstrap.min.css">
    <link rel="stylesheet" href="resources/css/custom.css">
</head>

<body>
    <div id="logout">
        <span id="nome-usuario" style="opacity: 0;">Nome: {{ $nome }}</span><span id="rf-usuario" class="mx-2">{{ $rf }}</span>
        <a href="logout.php"><button class="btn btn-danger btn-sm float-right">Sair</button></a>
    </div>
    <div id="app">
        <div class="row w-75 mx-auto">
            <div class="col-4">
                <img src="resources/img/logo_prefeitura.png" alt="PMSP">
            </div>
            <div class="col">
                <h1>DAPL - Dados Abertos de Processos de Licenciamento</h1>
            </div>
        </div>
        <div class="card w-75 mx-auto mt-4">
            @include('partials.header', ['active' => 'businessintelligence'])

            <!-- Consulta processos -->
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <label class="form-label" title="Setor, Quadra, Lote">SQL</label>
                        <input type="text" class="form-control" v-model="sqlBusca">
                    </div>
                    <div class="col">
                        <button class="btn btn-info btn-lg mt-3" @click="buscarSql">Buscar</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div
                        class="text-center m-4"
                         v-show="jaBuscou && (!processosLocalizados || processosLocalizados.length == 0)">
                            Nenhum processo localizado
                        </div>
                        <table class="table mt-3" v-show="processosLocalizados.length > 0">
                            <tr>
                                <th>Processo</th>
                                <th>SQL</th>
                                <th>Sistema</th>
                                <th>Assunto</th>
                                <th>Data Protocolo</th>
                            </tr>
                            <tr v-for="processo in processosLocalizados">
                                <td>@{{processo.processo}}</td>
                                <td>@{{processo.sql}}</td>
                                <td>@{{processo.sistema}}</td>
                                <td>@{{processo.assunto}}</td>
                                <td>@{{ formatarData(processo.dtPedidoProtocolo) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Dashboard B.I. -->
        </div>
    </div>
</body>
<script src="resources/js/bootstrap.bundle.min.js"></script>
<script src="resources/js/vue.global.js"></script>
<script>
    const {
        createApp,
        reactive,
        ref
    } = Vue

    createApp({
        data() {
            return {
                isCarregando: false,
                jaBuscou: false,
                processosLocalizados: [],
                sqlBusca: ""
            }
        },
        methods: {
            async buscarSql() {
                try {
                    if (this.sqlBusca.length < 10) {
                        window.alert("Digite um SQL válido");
                        return;
                    }
                    let urlBuscarSql = 'api/bi/buscarsql';
                    urlBuscarSql += "?sql_incra="+this.sqlBusca;

                    this.isCarregando = true;

                    const response = await fetch(urlBuscarSql);
                    if (!response.ok) throw new Error('Erro ao carregar dados');

                    const lista = await response.json();
                    console.log('DADOS: ', lista)
                    this.isCarregando = false;

                    lista.sort((a, b) => new Date(b.dtPedidoProtocolo) - new Date(a.dtPedidoProtocolo));

                    this.processosLocalizados = lista;

                    this.$forceUpdate();
                } catch (error) {
                    console.error('Erro na requisição:', error);
                    window.alert("FALHA AO CARREGAR: " + error);
                    this.isCarregando = false;
                } finally {
                    this.jaBuscou = true;
                }
            },
            formatarData(dataCrua) {
                try {
                    let retorno = new Date(dataCrua).toLocaleDateString("pt-BR");
                    return retorno;
                }
                catch(err) {
                    console.warn("Erro ao formatar data:", dataCrua, err);
                    return dataCrua;
                }
            },
            txtLargura(numero) {
                return `width: ${numero}%;`
            }
        },
        mounted() {
            // Verificar acesso
        },
        computed: {
            percentualConclusaoHisHmp() {
                if (this.totalHisHmp === 0) return 0;
                return ((this.totalValidado / this.totalHisHmp) * 100).toFixed(2);
            }
        }
    }).mount('#app')
</script>

</html>