#!/bin/bash
set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
NC='\033[0m'

IMAGE_NAME="lab-cmdi"
CONTAINER_NAME="lab-cmdi"
PORT="8084"

echo -e "${CYAN}"
echo "╔══════════════════════════════════════════╗"
echo "║   Lab Command Injection — IDN Lab        ║"
echo "╚══════════════════════════════════════════╝"
echo -e "${NC}"

if ! command -v docker &> /dev/null; then
    echo -e "${RED}[x] Docker tidak ditemukan.${NC}"
    echo "    curl -fsSL https://get.docker.com | sh"
    echo "    sudo usermod -aG docker \$USER && exit"
    exit 1
fi

if docker ps -a --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    echo -e "${YELLOW}[*] Menghapus container lama...${NC}"
    docker stop $CONTAINER_NAME 2>/dev/null || true
    docker rm $CONTAINER_NAME 2>/dev/null || true
fi

echo -e "${YELLOW}[*] Building Docker image...${NC}"
docker build -t $IMAGE_NAME .

echo -e "${YELLOW}[*] Menjalankan container di port ${PORT}...${NC}"
docker run -d \
    --name $CONTAINER_NAME \
    -p ${PORT}:80 \
    --restart unless-stopped \
    $IMAGE_NAME

echo -e "${YELLOW}[*] Menunggu service siap...${NC}"
for i in $(seq 1 20); do
    if curl -s -o /dev/null -w "%{http_code}" http://localhost:$PORT 2>/dev/null | grep -q "200"; then
        break
    fi
    printf "   Starting... (%d/20)\r" $i
    sleep 2
done

echo ""
VM_IP=$(hostname -I | awk '{print $1}')

echo -e "${GREEN}"
echo "╔══════════════════════════════════════════════════════╗"
echo "║   Lab Command Injection berhasil dijalankan!         ║"
echo "╠══════════════════════════════════════════════════════╣"
echo "║   Buka di browser:                                   ║"
printf "║   http://%-44s║\n" "${VM_IP}:${PORT}"
echo "║                                                      ║"
echo "║   Basic:                                             ║"
echo "║   /basic-1/    -> Basic CMDi           (Basic 1)    ║"
echo "║   /basic-2/    -> CMDi + Blind         (Basic 2)    ║"
echo "║   /basic-3/    -> CMDi + Input Fields  (Basic 3)    ║"
echo "║   Advanced:                                          ║"
echo "║   /advanced-1/ -> CMDi + Filter Bypass (Adv 1)      ║"
echo "║   /advanced-2/ -> CMDi + Blind OOB     (Adv 2)      ║"
echo "║   /advanced-3/ -> CMDi + WAF Evasion   (Adv 3)      ║"
echo "╠══════════════════════════════════════════════════════╣"
echo "║   docker logs -f lab-cmdi   (lihat log)              ║"
echo "║   docker stop lab-cmdi      (stop)                   ║"
echo "║   docker start lab-cmdi     (start ulang)            ║"
echo "╚══════════════════════════════════════════════════════╝"
echo -e "${NC}"
