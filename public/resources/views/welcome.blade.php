<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAPL - Dados Abertos de Processos de Licenciamento</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body>
    <div id="logout">
        <span id="nome-usuario">Nome: {{ $nome }}</span><span id="rf-usuario">{{ $rf }}</span>
        <a href="logout.php"><button class="btn btn-danger btn-sm float-right">Sair</button></a>
    </div>
    <div id="app">
        <div class="row w-75 mx-auto">
            <div class="col-4">
                <img src="resources/img/logo_prefeitura.png" alt="PMSP">
            </div>
            <div class="col">
                <h1>Validação de Processos</h1>
            </div>
        </div>
        <div class="card w-75 mx-auto mt-4">
            <div class="card-body px-4">
                <div class="card-title">
                    <h3>Processo: @{{ objProcesso.processo }} - @{{ objProcesso.assunto }}</h3>
                </div>
                <div class="row g-2">
                    <div class="col-2">
                        <label class="form-label">Data de Emissão</label>
                        <input type="date" class="form-control" v-model="objProcesso.dtEmissao" disabled>
                    </div>
                    <div class="col-2">
                        <label class="form-label">Blocos</label>
                        <input type="number" class="form-control" v-model="objProcesso.blocos">
                    </div>
                    <div class="col-2">
                        <label class="form-label">Pavimentos</label>
                        <input type="number" class="form-control" v-model="objProcesso.pavimentos">
                    </div>
                    <div class="col">
                        <label class="form-label">Unidades por categoria de uso</label>
                        <div class="row g-2" v-for="(item, index) in objProcesso.uniCatUso" :key="index">
                            <div class="col">
                                <select class="form-select" v-model="item.nome">
                                    <option disabled value="">Selecione</option>
                                    <option v-for="opcao in opcoesUniCatUso" :key="opcao" :value="opcao">@{{ opcao }}</option>
                                </select>
                            </div>
                            <div class="col">
                                <input type="number" class="form-control" v-model="item.valor">
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-primary mt-2" @click="adicionarUniCatUso" v-if="podeAdicionarUniCatUso">
                            + Adicionar
                        </button>
                    </div>
                </div>

                <div class="mt-4 row">
                    <div class="col mr-2" id="txt-aprovacao">
                        <div class="card">
                            <div class="card-header">Doc. Aprovação</div>
                            <div class="card-body txt-doc">
                                @{{objProcesso.docAprovacao}}
                            </div>
                        </div>
                    </div>
                    <div class="col ml-2" id="txt-conclusao">
                        <div class="card">
                            <div class="card-header">Doc. Conclusão</div>
                            <div class="card-body">
                                @{{objProcesso.docConclusao}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row my-4">
                    <div class="col text-center">
                        <button class="btn btn-success">Validar</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
    const {
        createApp,
        reactive,
        ref
    } = Vue

    createApp({
        data() {
            return {
                opcoesUniCatUso: ['HIS', 'HMP', 'EHIS', 'EHMP', 'R1', 'R2'],

                objProcesso: {
                    processo: "1234",
                    assunto: "Assunto X",
                    dtEmissao: "2025-10-10",
                    docAprovacao: "Lorem ipsum dolor sit amet consectetur, adipisicing elit. Dignissimos, enim qui nostrum architecto dolor fugiat rerum ducimus debitis itaque et veniam sed omnis repudiandae maiores nobis, excepturi quam illo nemo!",
                    docConclusao: "Lorem ipsum dolor sit amet consectetur, adipisicing elit. Dignissimos, enim qui nostrum architecto dolor fugiat rerum ducimus debitis itaque et veniam sed omnis repudiandae maiores nobis, excepturi quam illo nemo!",
                    uniCatUso: [{
                        nome: 'HIS',
                        valor: null
                    }],
                },
                processos: [{
                        processo: "1234",
                        assunto: "Assunto X",
                        dtEmissao: "2025-10-10",
                        doc_txt: "Lorem ipsum dolor sit amet consectetur, adipisicing elit. Dignissimos, enim qui nostrum architecto dolor fugiat rerum ducimus debitis itaque et veniam sed omnis repudiandae maiores nobis, excepturi quam illo nemo!",
                    },
                    {
                        processo: "1234",
                        assunto: "Assunto X",
                        dtEmissao: "2025-10-10",
                        doc_txt: "Lorem ipsum dolor sit amet consectetur, adipisicing elit. Dignissimos, enim qui nostrum architecto dolor fugiat rerum ducimus debitis itaque et veniam sed omnis repudiandae maiores nobis, excepturi quam illo nemo!",
                    },
                ]
            }
        },
        computed: {
            podeAdicionarUniCatUso() {
                const ultimo = this.objProcesso.uniCatUso[this.objProcesso.uniCatUso.length - 1];
                return ultimo.nome && ultimo.valor !== null;
            }
        },
        mounted() {
            this.carregarProcesso();
        },
        methods: {
            adicionarUniCatUso() {
                this.objProcesso.uniCatUso.push({
                    nome: '',
                    valor: null
                });
            },

            async carregarProcesso() {
                const rfValidador = document.getElementById('rf-usuario')?.textContent?.trim();

                if (!rfValidador) {
                    console.warn('rfValidador não encontrado');
                    return;
                }

                try {
                    const response = await fetch(`processoAValidar?rfValidador=${encodeURIComponent(rfValidador)}`);
                    if (!response.ok) throw new Error('Erro ao buscar processo');

                    const data = await response.json();
                    this.objProcesso = data.objProcesso;
                } catch (error) {
                    console.error('Erro na requisição:', error);
                }
            },

            mostrarAlerta() {
                alert(this.mensagem)
            },
            traduzir(campo) {
                switch (campo) {
                    case "doc_txt":
                        return "Texto do Documento"
                        break;

                    default:
                        return campo;
                        break;
                }
            }
        }

    }).mount('#app')
</script>

</html>
<style>
    body {
        background-color: #EEE;
    }

    #logout {
        position: absolute;
        right: 1rem;
    }

    #app {
        margin: 1rem;
    }

    #nome-usuario {
        color: #EEE;
    }

    .txt-doc {
        max-height: 30rem;
        overflow: auto;
    }
</style>