# Detectar el sistema operativo
ifeq ($(OS),Windows_NT)
    DETECTED_OS := Windows
else
    DETECTED_OS := $(shell uname -s)
endif

# Comando principal
run:
ifeq ($(DETECTED_OS),Windows)
	@echo --- Running on Windows ---
	@start.bat
else
	@echo --- Running on $(DETECTED_OS) ---
	@chmod +x start.sh
	@./start.sh
endif
