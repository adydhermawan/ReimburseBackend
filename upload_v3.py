import ftplib
import sys

# Credentials from .env.production
FTP_HOST = "ftpupload.net"
FTP_USER = "if0_40730583"
FTP_PASS = "jarangN0NT0N"
FTP_PORT = 21

def upload_v3():
    ftp = ftplib.FTP()
    try:
        print(f"Connecting to {FTP_HOST}...")
        ftp.connect(FTP_HOST, FTP_PORT)
        ftp.login(FTP_USER, FTP_PASS)
        
        local_file = "public/debug_login_v3.php"
        remote_file = "recashly.dadi.web.id/htdocs/public/debug_login_v3.php"
        
        print(f"Uploading {local_file} to {remote_file}...")
        with open(local_file, "rb") as f:
            ftp.storbinary(f"STOR {remote_file}", f)
            
        print("Upload successful!")
        
    except Exception as e:
        print(f"Error: {e}")
        sys.exit(1)
    finally:
        try:
            ftp.quit()
        except:
            pass

if __name__ == "__main__":
    upload_v3()
