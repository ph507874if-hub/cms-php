
document.addEventListener("DOMContentLoaded", () => {
    // Elementos da página
    const campoBusca = document.getElementById("buscarCategoria");
    const tabela = document.getElementById("tabelaCategorias");
    const modalExcluir = document.getElementById("modalExcluirCategoria");
    const formulario = document.getElementById("formCategoria");

    let categoriaParaExcluir = null;

    // Buscar e filtrar categorias
    if (campoBusca && tabela) {
        campoBusca.addEventListener("input", () => {
            const busca = campoBusca.value.toLowerCase().trim();
            const linhas = tabela.querySelectorAll("tbody tr");

            linhas.forEach((linha) => {
                const texto = linha.textContent.toLowerCase();
                linha.style.display = texto.includes(busca) ? "" : "none";
            });
        });
    }

    // Abrir modal de exclusão
    document.querySelectorAll(".btn-excluir-categoria").forEach((botao) => {
        botao.addEventListener("click", (evento) => {
            evento.preventDefault();

            categoriaParaExcluir =
                botao.dataset.id ||
                botao.getAttribute("data-categoria-id");

            if (modalExcluir) {
                modalExcluir.style.display = "flex";
                modalExcluir.setAttribute("aria-hidden", "false");
            } else if (categoriaParaExcluir) {
                const confirmar = confirm(
                    "Tem certeza de que deseja excluir esta categoria?"
                );

                if (confirmar) {
                    excluirCategoria(categoriaParaExcluir);
                }
            }
        });
    });

    // Cancelar exclusão
    const cancelarExclusao =
        document.getElementById("cancelarExcluirCategoria");

    if (cancelarExclusao) {
        cancelarExclusao.addEventListener("click", fecharModal);
    }

    // Confirmar exclusão
    const confirmarExclusao =
        document.getElementById("confirmarExcluirCategoria");

    if (confirmarExclusao) {
        confirmarExclusao.addEventListener("click", () => {
            if (categoriaParaExcluir) {
                excluirCategoria(categoriaParaExcluir);
            }
        });
    }

    function fecharModal() {
        if (modalExcluir) {
            modalExcluir.style.display = "none";
            modalExcluir.setAttribute("aria-hidden", "true");
        }

        categoriaParaExcluir = null;
    }

    // Enviar exclusão ao PHP
    function excluirCategoria(id) {
        // O formulário de exclusão deve apontar para o endpoint PHP correto.
        const formularioExcluir =
            document.getElementById("formExcluirCategoria");

        if (formularioExcluir) {
            const campoId =
                formularioExcluir.querySelector('[name="id"]') ||
                formularioExcluir.querySelector('[name="categoria_id"]');

            if (campoId) {
                campoId.value = id;
            }

            formularioExcluir.submit();
            return;
        }

        // Alternativa: formulário de exclusão específico por categoria.
        const formularioIndividual = document.getElementById(
            `formExcluirCategoria-${id}`
        );

        if (formularioIndividual) {
            formularioIndividual.submit();
            return;
        }

        alert(
            "Não foi encontrado o formulário de exclusão. " +
            "Configure o formExcluirCategoria no PHP."
        );
    }

    // Validar formulário de cadastro/edição
    if (formulario) {
        formulario.addEventListener("submit", (evento) => {
            const nome = formulario.querySelector(
                '[name="nome"], [name="categoria_nome"]'
            );

            if (nome && nome.value.trim() === "") {
                evento.preventDefault();
                alert("Digite o nome da categoria.");
                nome.focus();
                return;
            }

            if (nome) {
                nome.value = nome.value.trim();
            }
        });
    }

    // Fechar modal clicando fora dele
    if (modalExcluir) {
        modalExcluir.addEventListener("click", (evento) => {
            if (evento.target === modalExcluir) {
                fecharModal();
            }
        });
    }
});
