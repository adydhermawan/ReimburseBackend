import os
import ftplib
import sys

FTP_HOST = "ftpupload.net"
FTP_USER = "if0_40730583"
FTP_PASS = "jarangN0NT0N"
FTP_PORT = 21

def list_logs():
    print(f"Connecting to {FTP_HOST}...")
    try:
        ftp = ftplib.FTP()
        ftp.connect(FTP_HOST, FTP_PORT)
        ftp.login(FTP_USER, FTP_PASS)
        print("Logged in successfully.")
        
        print(f"Listing htdocs...")
        
        try:
            ftp.cwd("htdocs")
            ftp.retrlines('LIST')
        except ftplib.error_perm as e:
            print(f"Error changing directory or listing: {e}")
            sys.exit(1)
            
        ftp.quit()
        
    except Exception as e:
        print(f"Error: {e}")
        sys.exit(1)

if __name__ == "__main__":
    list_logs()
