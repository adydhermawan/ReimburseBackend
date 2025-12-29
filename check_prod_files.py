import ftplib
import sys

# FTP Credentials from .env.production (reusing from fetch_prod_logs.py)
FTP_HOST = "ftpupload.net"
FTP_USER = "if0_40730583"
FTP_PASS = "jarangN0NT0N"
FTP_PORT = 21

REMOTE_PATH = "recashly.dadi.web.id/htdocs/public"

def check_files():
    print(f"Connecting to {FTP_HOST}...")
    try:
        ftp = ftplib.FTP()
        ftp.connect(FTP_HOST, FTP_PORT)
        ftp.login(FTP_USER, FTP_PASS)
        print("Logged in successfully.")
        
        print(f"Listing contents of {REMOTE_PATH}:")
        ftp.cwd(REMOTE_PATH)
        ftp.retrlines("LIST")
        
        ftp.quit()
        
    except Exception as e:
        print(f"Error: {e}")
        sys.exit(1)

if __name__ == "__main__":
    check_files()
