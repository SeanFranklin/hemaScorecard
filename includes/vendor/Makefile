# This file mostly exists as executable documentation

.PHONY: all
all: foundation.min.css dataTables.foundation.min.css tinymce.min.js jquery-ui.js jquery-3.3.1.js jquery.dataTables.min.js

.PHONY: foundation.min.css
foundation.min.css:
	curl -L -o $@  https://cdnjs.cloudflare.com/ajax/libs/foundation/6.4.3/css/foundation.min.css

.PHONY: dataTables.foundation.min.css
dataTables.foundation.min.css:
	curl -L -o $@  https://cdn.datatables.net/1.10.19/css/dataTables.foundation.min.css

# Chivo came from Google Fonts, but that's a highly manual process

.PHONY: tinymce.min.js
tinymce.min.js:
	curl -L -o $@ https://cdn.tiny.cloud/1/ctrvec03t4hztqmygiaf7d6mtiod1qat9px92nlsxdq2mat3/tinymce/6/tinymce.min.js

.PHONY: jquery-ui.js
jquery-ui.js:
	curl -L -o $@ https://code.jquery.com/ui/1.10.4/jquery-ui.js

.PHONY: jquery-3.3.1.js
jquery-3.3.1.js:
	curl -L -o $@ https://code.jquery.com/jquery-3.3.1.js

.PHONY: jquery.dataTables.min.js
jquery.dataTables.min.js:
	curl -L -o $@ https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js