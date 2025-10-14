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
                <img src="../resources/img/logo_prefeitura.png" alt="PMSP">
            </div>
            <div class="col">
                <h1>Validação de Processos</h1>
            </div>
        </div>
        <div class="card w-75 mx-auto mt-4">
            <div class="card-body" v-show="isCarregando">
                <h2 class="text-center">@{{msgStatus}}</h2>
                <div class="d-flex justify-content-center align-items-center"
                    v-if="msgStatus = 'Carregando...'"
                    style="height: 10vh;">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
            </div>
            <div class="card-body px-4" v-show="!isCarregando">
                <div id="fixada" style="position: sticky; top: 0; background-color: white; z-index: 10; padding: 10px; border-bottom: 1px solid #ccc;">
                    <div class="card-title">
                        <h3>Processo: @{{ objProcesso.processo }} - @{{ objProcesso.assunto }}</h3>
                    </div>
                    <hr>
                    <h4>Categoria identificada: @{{objProcesso.categoria}}</h4>
                    <hr>
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
                            <br>
                            <button class="btn btn-sm btn-outline-primary mt-0" @click="adicionarUniCatUso" v-if="podeAdicionarUniCatUso">
                                + Adicionar
                            </button>
                        </div>
                    </div>

                    <div class="row my-4">
                        <div class="col">
                            <button class="btn btn-success btn-lg w-100" @click="validarProcesso">Validar</button>
                        </div>
                    </div>
                </div>

                <div class="mt-4 row">
                    <div class="col ml-2" id="txt-conclusao">
                        <div class="card">
                            <div class="card-header">Certificado de Conclusão</div>
                            <div class="card-body txt-doc">
                                <!-- @{{objProcesso.docConclusao}} -->
                                <p v-html="destacarTermos(objProcesso.docConclusao)" style="white-space: pre-line;"></p>
                            </div>
                        </div>
                    </div>
                    <div class="col mr-2" id="txt-aprovacao">
                        <div class="card">
                            <div class="card-header">Doc Relacionado</div>
                            <div class="card-body txt-doc">
                                <!-- <p style="white-space: pre-line;">@{{ objProcesso.docAprovacao }}</p> -->
                                <p v-html="destacarTermos(objProcesso.docAprovacao)" style="white-space: pre-line;"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div v-if="objProcesso.docsRelacionados && objProcesso.docsRelacionados.length > 0">
                    <h5>Docs Relacionados ao SQL</h5>
                    <div class="mt-4 row" v-for="docRelacionado in objProcesso.docsRelacionados">
                        <div class="col mr-2">
                            <div class="card">
                                <div class="card-header">@{{docRelacionado.assunto}} - @{{docRelacionado.dtEmissao}}</div>
                                <div class="card-body txt-doc">
                                    <p v-html="destacarTermos(docRelacionado.doc_txt)" style="white-space: pre-line;"></p>
                                </div>
                            </div>
                        </div>
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
                opcoesUniCatUso: ['EHIS', 'EHMP', 'HIS', 'HMP', 'R1', 'R2'],

                isCarregando: false,
                msgStatus: 'Carregando...',

                objProcesso: {
                    processo: "1234",
                    assunto: "Assunto X",
                    dtEmissao: "2025-10-10",
                    blocos: null,
                    pavimentos: null,
                    docAprovacao: "Lorem ipsum dolor sit amet consectetur, adipisicing elit. Dignissimos, enim qui nostrum architecto dolor fugiat rerum ducimus debitis itaque et veniam sed omnis repudiandae maiores nobis, excepturi quam illo nemo!",
                    docConclusao: "Lorem ipsum dolor sit amet consectetur, adipisicing elit. Dignissimos, enim qui nostrum architecto dolor fugiat rerum ducimus debitis itaque et veniam sed omnis repudiandae maiores nobis, excepturi quam illo nemo!",
                    uniCatUso: [{
                        nome: 'HIS',
                        valor: null
                    }],
                }
            }
        },
        computed: {
            podeAdicionarUniCatUso() {
                const ultimo = this.objProcesso.uniCatUso[this.objProcesso.uniCatUso.length - 1];
                if (!ultimo) {
                    return true;
                }
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
                    this.isCarregando = true;
                    const response = await fetch(`api/processoAValidar?rfValidador=${encodeURIComponent(rfValidador)}`);
                    if (!response.ok) throw new Error('Erro ao buscar processo');

                    const data = await response.json();
                    this.objProcesso = data.objProcesso;

                    // this.objProcesso.categoria = this.extrairLinha21(data.objProcesso.docAprovacao);

                    if (!this.objProcesso.uniCatUso) {
                        this.objProcesso.uniCatUso.push({
                            nome: 'HIS',
                            valor: null
                        })
                    }
                    this.procurarCatUso();
                    this.objProcesso.blocos = this.procurarBlocos();
                    this.objProcesso.pavimentos = this.procurarPavimentos();
                    // this.objProcesso.docAprovacao = this.objProcesso.docAprovacao?.replace(/\r?\n/g, '<br>') || '';
                    // this.objProcesso.docConclusao = this.objProcesso.docConclusao?.replace(/\r?\n/g, '<br>') || '';
                    this.isCarregando = false;
                    this.$forceUpdate();
                } catch (error) {
                    console.error('Erro na requisição:', error);
                    window.alert("FALHA AO CARREGAR: " + error);
                    this.isCarregando = false;
                }
            },


            destacarTermos(texto) {
                if (!texto) return '';

                const termos = {
                    'PAVIMENTO': 'destaque-1',
                    'ANDAR': 'destaque-1',
                    'BLOCO': 'destaque-2',
                    'PREDIO': 'destaque-2',
                    'PRÉDIO': 'destaque-2',
                    'UNIDADES': 'destaque-3',
                    'HMP': 'destaque-4',
                    'HIS': 'destaque-5',
                };

                // const regex = new RegExp(`(${termo})`, 'gi');
                // return texto.replace(regex, '<span class="destaque">$1</span>');

                let resultado = texto;

                for (const [palavra, classe] of Object.entries(termos)) {
                    const regex = new RegExp(`(${palavra})`, 'gi');
                    resultado = resultado.replace(regex, `<span class="${classe}">$1</span>`);
                }

                return resultado;

            },

            // extrairLinha21($texto) {
            //     $linhas = preg_split('/\r\n|\r|\n/', $texto);
            //     return count($linhas) >= 21 ? trim($linhas[20]) : null;
            // },


            encontrarNumeroAntesDaPalavra(texto, palavraChave) {
                const regex = new RegExp(`(\\d+(?:[.,]\\d+)?)\\s+${palavraChave}`, 'i');
                const match = texto.match(regex);
                return match ? parseFloat(match[1].replace(',', '.')) : null;
            },

            encontrarNumeroDepoisDaPalavra(texto, palavraChave) {
                const textoUpper = texto.toUpperCase();
                const palavra = palavraChave.toUpperCase();

                // Expressão regular:
                // - busca a palavra
                // - permite até 10 caracteres após ela (qualquer caractere)
                // - dentro desses 10 caracteres, procura o primeiro número inteiro
                const regex = new RegExp(`${palavra}[^\\d]{0,10}(\\d+)`);

                const match = textoUpper.match(regex);
                return match ? parseInt(match[1], 10) : null;
            },

            melhorCorrespondenciaCatUso(texto) {
                try {
                    let txtRetorno = texto.toUpperCase().replace(/[.\s]/g, '');

                    for (const palavra of this.opcoesUniCatUso) {
                        if (txtRetorno.includes(palavra)) {
                            return palavra;
                        }
                    }

                    console.log("txt retorno: ", txtRetorno)

                    return txtRetorno;
                } catch (err) {
                    console.warn("FALHA AO LIMPAR: ", texto, err);
                    return texto;
                }
            },

            mostrarAlerta() {
                alert(this.mensagem)
            },

            procurarCatUso() {
                try {
                    let catUsoPrincipal = this.melhorCorrespondenciaCatUso(this.objProcesso.categoria);
                    this.objProcesso.uniCatUso[0].nome = catUsoPrincipal;
                    this.objProcesso.uniCatUso[0].valor = this.procurarUnidades();
                } catch (err) {
                    console.warn("Não achou catUso", err);
                }
            },

            procurarUnidades() {
                try {
                    let numUnidades = this.encontrarNumeroAntesDaPalavra(this.objProcesso.docConclusao, 'UNIDADE');
                    if (!numUnidades) {
                        numUnidades = this.encontrarNumeroAntesDaPalavra(this.objProcesso.docAprovacao, 'UNIDADE');
                    }
                    if (!numUnidades) {
                        numUnidades = this.encontrarNumeroDepoisDaPalavra(this.objProcesso.docAprovacao, 'UNIDADE');
                    }
                    return numUnidades;
                } catch (err) {
                    console.warn("procurar unidades: ", err);
                }
                return null;
            },
            procurarBlocos() {
                try {
                    let numBlocos = this.encontrarNumeroAntesDaPalavra(this.objProcesso.docConclusao, 'PREDIO');
                    if (!numBlocos) {
                        numBlocos = this.encontrarNumeroAntesDaPalavra(this.objProcesso.docConclusao, 'BLOCO');
                    }
                    if (!numBlocos) {
                        numBlocos = this.encontrarNumeroAntesDaPalavra(this.objProcesso.docAprovacao, 'PREDIO');
                    }
                    if (!numBlocos) {
                        numBlocos = this.encontrarNumeroAntesDaPalavra(this.objProcesso.docAprovacao, 'BLOCO');
                    }
                    // DEPOIS DA PALAVRA
                    if (!numBlocos) {
                        numBlocos = this.encontrarNumeroDepoisDaPalavra(this.objProcesso.docConclusao, 'PREDIO');
                    }
                    if (!numBlocos) {
                        numBlocos = this.encontrarNumeroDepoisDaPalavra(this.objProcesso.docConclusao, 'BLOCO');
                    }
                    if (!numBlocos) {
                        numBlocos = this.encontrarNumeroDepoisDaPalavra(this.objProcesso.docAprovacao, 'PREDIO');
                    }
                    if (!numBlocos) {
                        numBlocos = this.encontrarNumeroDepoisDaPalavra(this.objProcesso.docAprovacao, 'BLOCO');
                    }
                    // FIM DEPOIS DA PALAVRA
                    if (!numBlocos) {
                        numBlocos = 1;
                    }
                    return numBlocos;
                } catch (err) {
                    console.warn("procurar Blocos: ", err);
                }
                return null;
            },
            procurarPavimentos() {
                try {
                    let numPavimentos = this.encontrarNumeroAntesDaPalavra(this.objProcesso.docConclusao, 'ANDAR');
                    if (!numPavimentos) {
                        numPavimentos = this.encontrarNumeroAntesDaPalavra(this.objProcesso.docConclusao, 'PAVIMENTO');
                    }
                    if (!numPavimentos) {
                        numPavimentos = this.encontrarNumeroAntesDaPalavra(this.objProcesso.docAprovacao, 'ANDAR');
                    }
                    if (!numPavimentos) {
                        numPavimentos = this.encontrarNumeroAntesDaPalavra(this.objProcesso.docAprovacao, 'PAVIMENTO');
                    }
                    // DEPOIS PALAVRA
                    if (!numPavimentos) {
                        numPavimentos = this.encontrarNumeroDepoisDaPalavra(this.objProcesso.docConclusao, 'ANDAR');
                    }
                    if (!numPavimentos) {
                        numPavimentos = this.encontrarNumeroDepoisDaPalavra(this.objProcesso.docConclusao, 'PAVIMENTO');
                    }
                    if (!numPavimentos) {
                        numPavimentos = this.encontrarNumeroDepoisDaPalavra(this.objProcesso.docAprovacao, 'ANDAR');
                    }
                    if (!numPavimentos) {
                        numPavimentos = this.encontrarNumeroDepoisDaPalavra(this.objProcesso.docAprovacao, 'PAVIMENTO');
                    }
                    // FIM DEPOIS PALAVRA
                    if (!numPavimentos) {
                        return 1;
                    }
                    return numPavimentos;
                } catch (err) {
                    console.warn("procurar Pavimentos: ", err);
                }
                return null;
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
            },

            async validarProcesso() {
                try {
                    this.isCarregando = true;
                    this.msgStatus = 'Validando informações...';
                    // const response = await this.$axios.post('/validarProcesso', {
                    //     objProcesso: this.objProcesso
                    // });

                    const response = await fetch('api/validarProcesso', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            objProcesso: this.objProcesso
                        })
                    });


                    // Se chegou aqui, foi validado com sucesso
                    // window.alert('Validado com sucesso!');
                    // window.location.reload();


                    if (!response.ok) throw new Error('Erro na requisição');

                    const data = await response.json();
                    window.alert('Validado com sucesso!');
                    window.location.reload();

                } catch (error) {
                    console.error("ERRO AO VALIDAR: ", error);
                    this.msgStatus = error.response?.data?.message || 'Erro ao validar processo.';
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

    .destaque {
        background-color: yellow;
        font-weight: bold;
    }

    .destaque-1 {
        background-color: #F55;
        font-weight: bold;
    }

    .destaque-2 {
        background-color: cyan;
        font-weight: bold;
    }

    .destaque-3 {
        background-color: #C5E;
        font-weight: bold;
    }

    .destaque-4 {
        background-color: yellow;
        font-weight: bold;
    }

    .destaque-5 {
        background-color: yellow;
        font-weight: bold;
    }
</style>