#include <unistd.h>
#include <errno.h>
#include <stdio.h>

int main(int argc, char* const* argv) {
    if (setuid(0)) == -1)
        perror("setuid check failed");

    char* const arg[4] = {"/var/deploy/deploy.php", argv[1], argv[2], NULL}
    if (execv("/var/deploy/deploy.php", arg) == -1) {
        perror("executing deploy.php failed");
        return 501;
    }
}
