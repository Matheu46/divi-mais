document.addEventListener('DOMContentLoaded', () => {
  const media = wp.media;

  // Estende o filtro padrão
  media.view.AttachmentFilters.FolderFilter = media.view.AttachmentFilters.extend({
      createFilters() {
          const filters = {
              all: {
                  text: 'Todas as Pastas FileBird',
                  props: { filebird_folder: '' }
              }
          };

          // Função recursiva para "achatar" a árvore
          function traverse(folders, depth = 0) {
              folders.forEach(folder => {
                  const key = String(folder.id);
                  // adiciona traços para indicar profundidade (opcional)
                  const label = `${'— '.repeat(depth)}${folder.text}`;
                  filters[key] = {
                      text: label,
                      props: { filebird_folder: folder.id }
                  };
                  if (folder.children && folder.children.length) {
                      traverse(folder.children, depth + 1);
                  }
              });
          }

          if (window.FilebirdFolders?.folders?.length) {
              traverse(window.FilebirdFolders.folders);
          }

          this.filters = filters;
      }
  });

  // Injeta o filtro na toolbar da Biblioteca de Mídia
  const originalCreateToolbar = media.view.AttachmentsBrowser.prototype.createToolbar;
  media.view.AttachmentsBrowser.prototype.createToolbar = function () {
      originalCreateToolbar.call(this);
      this.toolbar.set('FolderFilter', new media.view.AttachmentFilters.FolderFilter({
          controller: this.controller,
          model: this.collection.props,
          priority: -75
      }));
  };
});