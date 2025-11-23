from flask import (
    Flask, render_template, redirect, url_for, flash,
    request, session, abort, send_from_directory, jsonify, current_app as app
)
from flask_wtf import FlaskForm
from flask_wtf.file import FileField, FileAllowed, FileRequired

from wtforms import (
    StringField, PasswordField, SubmitField, SelectField, HiddenField,
    DecimalField, TextAreaField
)
from wtforms.validators import (
    DataRequired, Email, Length, EqualTo, NumberRange, Optional
)

from werkzeug.security import generate_password_hash, check_password_hash
from werkzeug.utils import secure_filename

import os, time

from decimal import Decimal
from pathlib import Path

import mysql.connector
from mysql.connector import Error
from contextlib import closing
from functools import wraps

import os
from openai import OpenAI
# ===================== App & Config =====================
app = Flask(__name__)

from pathlib import Path
from tempfile import gettempdir
from flask_socketio import SocketIO, emit, join_room, leave_room

from itsdangerous import URLSafeTimedSerializer, BadSignature, SignatureExpired
import smtplib
from email.mime.text import MIMEText
from email.utils import formataddr
from datetime import datetime


app.config.update(
    MAIL_SERVER     = "smtp.gmail.com",
    MAIL_PORT       = 587,
    MAIL_USE_TLS    = True,       # Gmail dùng TLS port 587
    MAIL_USERNAME   = "quoclong.laravel06@gmail.com",
    MAIL_PASSWORD   = "quzeewueawptbekn",   # App password, không phải password Gmail thường
    MAIL_FROM       = "Trao Đổi Đồ Cũ <quoclong.laravel06@gmail.com>",
    VERIFY_EXPIRES  = 60 * 60 * 24,  # 24 giờ
)

def _get_serializer():
    return URLSafeTimedSerializer(app.config["SECRET_KEY"], salt="verify-email")

def generate_verify_token(user_id: int, email: str) -> str:
    s = _get_serializer()
    # Nhúng cả user_id và email vào token
    return s.dumps({"uid": user_id, "email": email.lower().strip()})

def verify_token(token: str, max_age: int = None):
    s = _get_serializer()
    try:
        data = s.loads(token, max_age=max_age or app.config["VERIFY_EXPIRES"])
        return data  # {"uid":..., "email":...}
    except SignatureExpired:
        return None  # quá hạn
    except BadSignature:
        return None  # token không hợp lệ

import traceback
from email.header import Header

def send_email(to_email: str, subject: str, html_body: str):
    # Header From hiển thị: có tên + email
    display_from = formataddr((str(Header("Trao Đổi Đồ Cũ", "utf-8")), app.config["MAIL_USERNAME"]))
    envelope_from = app.config["MAIL_USERNAME"]  # chỉ email, không có display name

    msg = MIMEText(html_body, "html", "utf-8")
    msg["Subject"] = Header(subject, "utf-8")
    msg["From"] = display_from
    msg["To"] = to_email

    try:
        server = smtplib.SMTP(app.config["MAIL_SERVER"], app.config["MAIL_PORT"], timeout=15)
        server.set_debuglevel(1)   # in log SMTP để debug
        server.ehlo()
        if app.config.get("MAIL_USE_TLS", True):
            server.starttls()
            server.ehlo()
        server.login(app.config["MAIL_USERNAME"], app.config["MAIL_PASSWORD"])
        server.sendmail(envelope_from, [to_email], msg.as_string())
    except Exception as e:
        print("SMTP send error:", repr(e))
        traceback.print_exc()
        raise
    finally:
        try: server.quit()
        except Exception: pass

@app.route("/verify-email")
def verify_email():
    token = (request.args.get("token") or "").strip()
    data = verify_token(token)
    if not data:
        flash("Liên kết xác minh không hợp lệ hoặc đã hết hạn.", "danger")
        return redirect(url_for("login"))

    uid  = int(data.get("uid", 0))
    mail = (data.get("email") or "").strip().lower()
    if uid <= 0 or not mail:
        flash("Token không hợp lệ.", "danger")
        return redirect(url_for("login"))

    with closing(get_conn()) as conn, closing(conn.cursor(dictionary=True)) as cur:
        cur.execute("SELECT id, email, email_verified_at FROM users WHERE id=%s", (uid,))
        u = cur.fetchone()
        if not u or (u["email"] or "").lower().strip() != mail:
            flash("Tài khoản hoặc email không khớp.", "danger")
            return redirect(url_for("login"))

        if u["email_verified_at"]:
            flash("Email đã được xác minh trước đó. Hãy đăng nhập.", "info")
            return redirect(url_for("login"))

        cur2 = conn.cursor()
        cur2.execute("UPDATE users SET email_verified_at = NOW() WHERE id=%s", (uid,))
        conn.commit()

    flash("Xác minh email thành công! Bây giờ bạn có thể đăng nhập.", "success")
    return redirect(url_for("login"))


def send_verification_email(user_id: int, fullname: str, email: str):
    token = generate_verify_token(user_id, email)
    verify_link = url_for("verify_email", token=token, _external=True)

    html = f"""
    <div style="font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:auto">
      <h2>Chào {fullname},</h2>
      <p>Cảm ơn bạn đã đăng ký tài khoản trên <b>Trao đổi đồ cũ</b>.</p>
      <p>Nhấn vào nút dưới đây để xác minh email của bạn (hiệu lực trong 24 giờ):</p>
      <p>
        <a href="{verify_link}" 
           style="display:inline-block;background:#2563eb;color:#fff;padding:12px 18px;border-radius:8px;text-decoration:none">
           Xác minh email
        </a>
      </p>
      <p>Nếu nút không bấm được, mở liên kết này:</p>
      <p><a href="{verify_link}">{verify_link}</a></p>
      <hr>
      <small>Nếu bạn không thực hiện đăng ký, hãy bỏ qua email này.</small>
    </div>
    """

    send_email(email, "Xác minh email của bạn", html)

    # (tùy chọn) lưu thời điểm gửi
    try:
        with closing(get_conn()) as conn, closing(conn.cursor()) as cur:
            cur.execute("UPDATE users SET verify_sent_at = NOW() WHERE id=%s", (user_id,))
            conn.commit()
    except Exception:
        pass
    
    
socketio = SocketIO(app, cors_allowed_origins="*", async_mode="eventlet")
# ========================= chatbot =========================
client = OpenAI(api_key="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")



# ===================== Socket.IO Events =====================
@socketio.on("connect")
def sio_connect():
    u = session.get("user")
    who = (u and u.get("fullname")) or "Guest"
    print("Connected:", who)


@socketio.on("leave_room")
def sio_leave_room(data):
    room = (data or {}).get("room")
    if not room: return
    leave_room(room)
@socketio.on("send_message")
def sio_send_message(data):
    text = (data or {}).get("text","").strip()
    room = (data or {}).get("room")
    if not text or not room:
        return

    u = session.get("user")
    sender = {
        "id": (u and u.get("id")),
        "name": (u and u.get("fullname")) or "Guest"
    }

    # ✅ Lưu tin nhắn vào DB
    save_message(room, sender["id"], text)

    emit("new_message", {
        "room": room,
        "sender": sender,
        "text": text,
        "ts": int(time.time())
    }, room=room)
    
    
def ensure_media_root():
    candidates = [
        Path(app.instance_path) / "uploads",
        Path(app.root_path) / "media",
        Path(gettempdir()) / "traodoi_uploads",
    ]
    for p in candidates:
        try:
            p.mkdir(parents=True, exist_ok=True)
            t = p / ".write_test"
            t.write_bytes(b"ok")
            t.unlink(missing_ok=True)
            app.config["MEDIA_ROOT"] = str(p)
            print("MEDIA_ROOT =>", app.config["MEDIA_ROOT"])
            return p
        except Exception as e:
            print("MEDIA candidate failed:", p, "=>", e)
    raise RuntimeError("No writable MEDIA_ROOT found")

MEDIA_DIR = ensure_media_root()
ALLOWED_EXTS = ["jpg","jpeg","png","webp"]
app.config["MAX_CONTENT_LENGTH"] = 5 * 1024 * 1024  # 5MB

def _save_image(file_storage):
    """
    Lưu file vào MEDIA_ROOT và trả về CHỈ tên file (vd: '5_1726722333.jpg').
    Trả None nếu không có file hoặc định dạng không hợp lệ.
    """
    if not file_storage or not getattr(file_storage, "filename", ""):
        return None

    filename = secure_filename(file_storage.filename)
    if "." not in filename:
        flash("File ảnh không hợp lệ.", "warning")
        return None

    ext = filename.rsplit(".", 1)[-1].lower()
    if ext not in ALLOWED_EXTS:
        flash("Định dạng ảnh không hỗ trợ. Chỉ jpg, jpeg, png, webp.", "warning")
        return None

    root = Path(app.config["MEDIA_ROOT"])
    try:
        root.mkdir(parents=True, exist_ok=True)
    except Exception:
        # fallback lại nếu thư mục bị xóa khi đang chạy
        root = ensure_media_root()

    new_name = f"{session['user']['id']}_{int(time.time())}.{ext}"
    target = root / new_name
    target.parent.mkdir(parents=True, exist_ok=True)

    try:
        try: file_storage.stream.seek(0, os.SEEK_SET)
        except Exception: pass
        print("Saving to:", target, "| parent exists?", target.parent.exists())
        with open(target, "wb") as f:
            f.write(file_storage.read())
    except Exception as e:
        print("SAVE ERROR:", e)
        flash("Không thể lưu ảnh lên máy chủ.", "danger")
        return None

    return new_name

app.config["SECRET_KEY"] = "dev-secret-key-change-me"  # Đổi khi deploy
# Một số bảo vệ session cơ bản
app.config.update(
    SESSION_COOKIE_HTTPONLY=True,
    SESSION_COOKIE_SAMESITE="Lax",
)




# ===================== MySQL (XAMPP) =====================
DB_CONFIG = dict(
    host="localhost",
    user="root",          # mặc định XAMPP
    password="",          # nếu MySQL có mật khẩu thì sửa tại đây
    database="user_manager",
    auth_plugin="mysql_native_password",  # nếu lỗi auth có thể bỏ dòng này
)

def get_conn():
    """Mở connection tới MySQL theo DB_CONFIG."""
    return mysql.connector.connect(**DB_CONFIG)

# ================== HÀM NẠP DANH MỤC ==================
def load_categories():
    """
    Lấy toàn bộ categories từ DB, trả về list[dict] dạng:
    [
      {"icon": "📚", "name": "Sách", "slug": "book"},
      {"icon": "👗", "name": "Thời trang nữ", "slug": "fashion_women"},
      ...
      
    ]
    """
    sql = "SELECT icon, name, key_name AS slug FROM categories ORDER BY id ASC"
    with closing(get_conn()) as conn, closing(conn.cursor(dictionary=True)) as cur:
        cur.execute(sql)
        rows = cur.fetchall() or []

    CATEGORIES = [
        {"icon": r["icon"], "name": r["name"], "slug": r["slug"]}
        for r in rows
    ]
    return CATEGORIES

def load_category_maps(*, value="name"):
    """
    Trả về 2 cấu trúc tiện dùng:
    - cat_list: list[(value, label)] cho SelectField (value = 'name' hoặc 'slug')
    - cat_map:  dict {value: label} cho macro options_for/label_from
    """
    sql = "SELECT name, key_name FROM categories ORDER BY id ASC"
    with closing(get_conn()) as conn, closing(conn.cursor(dictionary=True)) as cur:
        cur.execute(sql)
        rows = cur.fetchall() or []

    if value == "slug":
        cat_list = [(r["key_name"], r["name"]) for r in rows]
        cat_map  = {r["key_name"]: r["name"] for r in rows}
    else:  # value = "name" (mặc định)
        cat_list = [(r["name"], r["name"]) for r in rows]
        cat_map  = {r["name"]: r["name"] for r in rows}
    return cat_list, cat_map

# ===================== WTForms =====================

def load_categories_for_form():
    """
    Trả về list[(value, label)] cho WTForms SelectField.
    Ở đây dùng cột `name` tiếng Việt làm cả value và label để bạn lưu thẳng tên vào listings.category.
    Nếu bạn muốn lưu slug, đổi SELECT cho lấy key_name và map (key_name, name).
    """
    sql = "SELECT name FROM categories ORDER BY id ASC"
    with closing(get_conn()) as conn, closing(conn.cursor()) as cur:
        cur.execute(sql)
        rows = cur.fetchall() or []
    return [(r[0], r[0]) for r in rows]  # [(value, label)]
CONDITIONS = [
    ("new","Mới 100%"),
    ("like_new","Như mới"),
    ("used","Đã qua sử dụng"),
    ("for_parts","Hỏng/để lấy linh kiện"),
]
# Nếu chưa có biến ALLOWED_EXTS ở phần cấu hình upload, thêm:
class RegisterForm(FlaskForm):
    fullname = StringField("Họ và tên", validators=[DataRequired(), Length(min=2, max=50)])
    email = StringField("Email", validators=[DataRequired(), Email(), Length(max=100)])
    password = PasswordField("Mật khẩu", validators=[DataRequired(), Length(min=6, max=64)])
    confirm  = PasswordField(
        "Nhập lại mật khẩu",
        validators=[DataRequired(), EqualTo("password", "Mật khẩu không khớp")]
    )
    submit = SubmitField("Tạo tài khoản")

class SellForm(FlaskForm):
    title = StringField("Tiêu đề", validators=[DataRequired(), Length(min=5, max=120)])
    description = TextAreaField("Mô tả chi tiết", validators=[DataRequired(), Length(min=10, max=5000)])
    price = DecimalField("Giá (VND)", places=0, rounding=None,
                         validators=[DataRequired(), NumberRange(min=0)])
    category = SelectField("Danh mục",  choices=[], coerce=str, validators=[DataRequired()])
    condition_level = SelectField("Tình trạng", choices=CONDITIONS, validators=[DataRequired()])
    location = StringField("Khu vực", validators=[Optional(), Length(max=100)])
    image = FileField("Ảnh bìa (jpg/png/webp)",
                      validators=[Optional(), FileAllowed(ALLOWED_EXTS, "Định dạng ảnh không hợp lệ")])
    submit = SubmitField("Đăng bán")

class LoginForm(FlaskForm):
    email = StringField("Email", validators=[DataRequired(), Email(), Length(max=100)])
    password = PasswordField("Mật khẩu", validators=[DataRequired(), Length(min=6, max=64)])
    submit = SubmitField("Đăng nhập")

# --- Form quản trị ---
class AdminEditUserForm(FlaskForm):
    fullname = StringField("Họ và tên", validators=[DataRequired(), Length(min=2, max=50)])
    email = StringField("Email", validators=[DataRequired(), Email(), Length(max=100)])
    role = SelectField("Quyền", choices=[("user","user"),("staff","staff"),("admin","admin")], validators=[DataRequired()])
    submit = SubmitField("Lưu thay đổi")

class AdminDeleteForm(FlaskForm):
    uid = HiddenField(validators=[DataRequired()])
    submit = SubmitField("Xoá")

# ===================== Helpers & Decorators =====================
def login_required(view):
    @wraps(view)
    def wrapper(*args, **kwargs):
        if not session.get("user"):
            flash("Vui lòng đăng nhập.", "warning")
            return redirect(url_for("login"))
        return view(*args, **kwargs)
    return wrapper

def roles_required(*allowed_roles):
    def decorator(view):
        @wraps(view)
        def wrapper(*args, **kwargs):
            u = session.get("user")
            if not u:
                flash("Vui lòng đăng nhập.", "warning")
                return redirect(url_for("login"))
            if u.get("role") not in allowed_roles:
                abort(403)
            return view(*args, **kwargs)
        return wrapper
    return decorator

@app.context_processor
def inject_user():
    
    u = session.get("user")
    def media_url(name):
        import os
        if not name: return None
        return url_for("uploaded_file", filename=os.path.basename(name))
    return dict(current_user=u, is_logged_in=bool(u), media_url=media_url)



# ===================== Routes cơ bản =====================
@app.route("/")
def home():
    u = session.get("user")
    cats = load_categories()  # nạp dữ liệu từ DB
    return render_template(
        "home.html",
        title="Trao đổi đồ cũ",
        cats=cats,
        user=u
    )


@app.route("/register", methods=["GET", "POST"])
def register():
    form = RegisterForm()
    if form.validate_on_submit():
        fullname = form.fullname.data.strip()
        email = form.email.data.strip().lower()
        password_hash = generate_password_hash(form.password.data)

        try:
            with closing(get_conn()) as conn:
                try:
                    conn.set_charset_collation('utf8mb4', 'utf8mb4_unicode_ci')
                except Exception:
                    pass
                with closing(conn.cursor(dictionary=True)) as cur:
                    # Kiểm tra trùng email
                    cur.execute("SELECT id FROM users WHERE email=%s", (email,))
                    if cur.fetchone():
                        flash("Email đã tồn tại. Vui lòng dùng email khác.", "danger")
                        return redirect(url_for("register"))

                    # Thêm mới (role mặc định: user)
                    cur.execute(
                        "INSERT INTO users(fullname, email, password_hash, role) VALUES (%s, %s, %s, %s)",
                        (fullname, email, password_hash, "user")
                    )
                    user_id = cur.lastrowid
                    conn.commit()

            # Gửi email xác minh
            try:
                send_verification_email(user_id, fullname, email)
                flash("Đăng ký thành công! Vui lòng kiểm tra email để xác minh tài khoản.", "success")
            except Exception as e:
                print("Send verify email error:", e)
                flash("Tạo tài khoản thành công, nhưng gửi email xác minh thất bại. Hãy thử 'Gửi lại email xác minh'.", "warning")

            return redirect(url_for("login"))

        except Error:
            flash("Không kết nối được MySQL hoặc lỗi truy vấn. Kiểm tra XAMPP và cấu hình DB.", "danger")

    return render_template("register.html", form=form)


@app.route("/need-verify")
@login_required
def need_verify():
    return render_template("need_verify.html")  # làm 1 trang nhỏ có nút "Gửi lại email xác minh"

@app.post("/resend-verify")
@login_required
def resend_verify():
    with closing(get_conn()) as conn, closing(conn.cursor(dictionary=True)) as cur:
        cur.execute("SELECT id, fullname, email, email_verified_at FROM users WHERE id=%s", (session["user"]["id"],))
        u = cur.fetchone()
    if not u:
        flash("Không tìm thấy tài khoản.", "danger")
        return redirect(url_for("login"))
    if u["email_verified_at"]:
        flash("Email đã xác minh rồi.", "info")
        return redirect(url_for("profile"))

    try:
        send_verification_email(u["id"], u["fullname"], u["email"])
        flash("Đã gửi lại email xác minh. Vui lòng kiểm tra hộp thư.", "success")
    except Exception as e:
        print("Resend error:", e)
        flash("Gửi lại thất bại. Hãy thử lại sau.", "danger")

    return redirect(url_for("need_verify"))


@app.route("/login", methods=["GET", "POST"])
def login():
    form = LoginForm()
    if form.validate_on_submit():
        email = form.email.data.strip().lower()
        password = form.password.data
        try:
            with closing(get_conn()) as conn:
                with closing(conn.cursor(dictionary=True)) as cur:
                    cur.execute(
                        "SELECT id, fullname, email, password_hash, role FROM users WHERE email=%s",
                        (email,)
                    )
                    user = cur.fetchone()
                    if not user or not check_password_hash(user["password_hash"], password):
                        flash("Email hoặc mật khẩu không đúng.", "danger")
                        return redirect(url_for("login"))

                    # Lưu session
                    session["user"] = {
                        "id": user["id"],
                        "fullname": user["fullname"],
                        "email": user["email"],
                        "role": user["role"]
                    }

                    # Điều hướng theo role
                    if user["role"] == "admin":
                        return redirect(url_for("admin_dashboard"))
                    elif user["role"] == "staff":
                        return redirect(url_for("staff_area"))
                    else:
                        return redirect(url_for("profile"))

        except Error:
            flash("Lỗi kết nối MySQL.", "danger")

    return render_template("login.html", form=form)

@app.route("/logout")
def logout():
    session.pop("user", None)
    flash("Bạn đã đăng xuất.", "info")
    return redirect(url_for("login"))

# ===================== Khu vực có phân quyền =====================
@app.route("/profile")
@login_required
def profile():
    return render_template("base.html", title=f"Hồ sơ - {session['user']['fullname']}")

@app.route("/staff-area")
@roles_required("admin", "staff")
def staff_area():
    return render_template("base.html", title="Khu vực nhân viên (Admin/Staff)")

@app.route("/admin")
@roles_required("admin")
def admin_dashboard():
    # Trang tổng quan admin, có link sang quản lý người dùng
    return render_template("base.html", title="Trang quản trị (Admin only)")

# ===================== Admin: quản lý người dùng =====================
@app.route("/admin/users")
@roles_required("admin")
def admin_users():
    q = request.args.get("q", "").strip()
    sql = "SELECT id, fullname, email, role, created_at FROM users"
    params = []
    if q:
        sql += " WHERE fullname LIKE %s OR email LIKE %s"
        like = f"%{q}%"
        params = [like, like]
    sql += " ORDER BY id DESC LIMIT 500"

    with closing(get_conn()) as conn, closing(conn.cursor(dictionary=True)) as cur:
        cur.execute(sql, params)
        rows = cur.fetchall()

    del_form = AdminDeleteForm()  # để render CSRF trong từng row
    return render_template("admin_users.html", rows=rows, q=q, del_form=del_form)

@app.route("/admin/users/<int:uid>")
@roles_required("admin")
def admin_user_view(uid):
    with closing(get_conn()) as conn, closing(conn.cursor(dictionary=True)) as cur:
        cur.execute("SELECT id, fullname, email, role, created_at FROM users WHERE id=%s", (uid,))
        u = cur.fetchone()
    if not u:
        flash("Người dùng không tồn tại.", "warning")
        return redirect(url_for("admin_users"))
    return render_template("admin_user_view.html", u=u)

@app.route("/admin/users/<int:uid>/edit", methods=["GET", "POST"])
@roles_required("admin")
def admin_user_edit(uid):
    with closing(get_conn()) as conn, closing(conn.cursor(dictionary=True)) as cur:
        cur.execute("SELECT id, fullname, email, role FROM users WHERE id=%s", (uid,))
        u = cur.fetchone()
        if not u:
            flash("Người dùng không tồn tại.", "warning")
            return redirect(url_for("admin_users"))

    form = AdminEditUserForm(data=u)
    if form.validate_on_submit():
        fullname = form.fullname.data.strip()
        email = form.email.data.strip().lower()
        role = form.role.data

        with closing(get_conn()) as conn, closing(conn.cursor(dictionary=True)) as cur:
            # Email trùng với tài khoản khác?
            cur.execute("SELECT id FROM users WHERE email=%s AND id<>%s", (email, uid))
            if cur.fetchone():
                flash("Email đã thuộc về tài khoản khác.", "danger")
                return redirect(url_for("admin_user_edit", uid=uid))

            cur.execute(
                "UPDATE users SET fullname=%s, email=%s, role=%s WHERE id=%s",
                (fullname, email, role, uid)
            )
            conn.commit()

        flash("Cập nhật người dùng thành công.", "success")
        return redirect(url_for("admin_users"))

    return render_template("admin_user_edit.html", form=form, uid=uid)

@app.route("/admin/users/<int:uid>/delete", methods=["POST"])
@roles_required("admin")
def admin_user_delete(uid):
    form = AdminDeleteForm()
    if not form.validate_on_submit() or int(form.uid.data) != uid:
        flash("Yêu cầu không hợp lệ.", "danger")
        return redirect(url_for("admin_users"))

    # Chặn tự xoá chính mình
    if session["user"]["id"] == uid:
        flash("Không thể tự xoá tài khoản đang đăng nhập.", "warning")
        return redirect(url_for("admin_users"))

    with closing(get_conn()) as conn, closing(conn.cursor()) as cur:
        cur.execute("DELETE FROM users WHERE id=%s", (uid,))
        conn.commit()

    flash("Đã xoá người dùng.", "success")
    return redirect(url_for("admin_users"))

@app.route("/uploads/<path:filename>")
def uploaded_file(filename):
    return send_from_directory(app.config["MEDIA_ROOT"], filename, as_attachment=False)
# ===================== Error handlers =====================
@app.errorhandler(403)
def forbidden(_):
    flash("Bạn không có quyền truy cập.", "danger")
    return redirect(url_for("home"))

@app.errorhandler(404)
def not_found(_):
    flash("Không tìm thấy trang bạn yêu cầu.", "warning")
    return redirect(url_for("home"))
@app.route("/sell", methods=["GET", "POST"])
@login_required
def sell():
    """
    Trang đăng bán sản phẩm:
    - Validate form
    - Lưu ảnh về instance/uploads (trả về tên file)
    - Ghi DB
    """
    form = SellForm()

    # ★ NẠP DANH MỤC TỪ SQL CHO SELECTFIELD
    form.category.choices = load_categories_for_form()
    if not form.category.choices:
        # fallback để không lỗi validate nếu DB đang rỗng
        form.category.choices = [("", "-- Chưa có danh mục --")]

    if form.validate_on_submit():
        # Lấy dữ liệu form
        title = form.title.data.strip()
        description = form.description.data.strip()

        # Ép giá về Decimal (nhận cả '3,500,000')
        from decimal import Decimal, InvalidOperation
        try:
            price = (
                Decimal(form.price.data)
                if isinstance(form.price.data, (int, float, Decimal))
                else Decimal(str(form.price.data).replace(",", "").strip())
            )
        except (InvalidOperation, ValueError):
            flash("Giá không hợp lệ.", "warning")
            return render_template("sell.html", form=form)

        category = form.category.data              # ★ giờ lấy từ SQL
        condition_level = form.condition_level.data
        location = (form.location.data or "").strip() or None

        # LƯU ẢNH BÌA — chỉ gọi MỘT lần
        cover_path = _save_image(form.image.data)
        print("MEDIA_ROOT =", app.config["MEDIA_ROOT"])
        print("filename  ->", getattr(form.image.data, "filename", None))
        print("saved as  ->", cover_path)

        # Ghi DB
        try:
            with closing(get_conn()) as conn:
                try:
                    conn.set_charset_collation('utf8mb4', 'utf8mb4_unicode_ci')
                except Exception:
                    pass
                with closing(conn.cursor()) as cur:
                    cur.execute(
                        """
                        INSERT INTO listings
                          (user_id, title, description, price, category, condition_level, location, cover_image, status)
                        VALUES
                          (%s, %s, %s, %s, %s, %s, %s, %s, 'active')
                        """,
                        (
                            session["user"]["id"],
                            title,
                            description,
                            str(price),
                            category,          # ★ lưu đúng theo value đã chọn
                            condition_level,
                            location,
                            cover_path,
                        ),
                    )
                    conn.commit()
            flash("Đăng bán thành công!", "success")
            return redirect(url_for("my_listings"))

        except Error as e:
            print("MySQL error at /sell:", e)
            flash("Không thể lưu tin đăng. Vui lòng kiểm tra kết nối MySQL.", "danger")

    elif request.method == "POST":
        # POST nhưng form không hợp lệ
        flash("Vui lòng kiểm tra lại các trường còn thiếu/không hợp lệ.", "warning")

    # GET hoặc lỗi -> render lại form
    return render_template("sell.html", form=form)


def get_category_choices():
    sql = "SELECT key_name, name FROM categories ORDER BY id"
    with closing(get_conn()) as conn, closing(conn.cursor()) as cur:
        cur.execute(sql)
        rows = cur.fetchall() or []
    # WTForms SelectField expects [(value, label), ...]
    return [(r[0], r[1]) for r in rows]


@app.route("/my/listings")
@login_required
def my_listings():
    """Danh sách tin đăng của chính người dùng hiện tại."""
    with closing(get_conn()) as conn, closing(conn.cursor(dictionary=True)) as cur:
        cur.execute("""
            SELECT id, title, price, status, created_at, cover_image
            FROM listings
            WHERE user_id = %s
            ORDER BY id DESC
        """, (session["user"]["id"],))
        rows = cur.fetchall()
    return render_template("my_listings.html", rows=rows)

#----------------- xem tn------------------
@app.route("/chat/with/<int:other_id>")
@login_required
def chat_with_user(other_id):
    me = session["user"]
    if other_id == me["id"]:
        flash("Không thể chat với chính mình.", "info")
        return redirect(url_for("chat_inbox"))

    # Lấy thông tin người còn lại
    with closing(get_conn()) as conn, closing(conn.cursor(dictionary=True)) as cur:
        cur.execute("SELECT id, fullname, email FROM users WHERE id=%s", (other_id,))
        other = cur.fetchone()
    if not other:
        flash("Người dùng không tồn tại.", "warning")
        return redirect(url_for("chat_inbox"))

    room = f"chat:{min(me['id'], other_id)}-{max(me['id'], other_id)}"
    history = load_messages(room, 50)

    # Tận dụng chatOtO.html (dùng title giả lập)
    fake_item = {"title": f"Trao đổi với {other['fullname']}"}
    return render_template(
        "chatOtO.html",
        item=fake_item,
        room=room,
        seller_name=other["fullname"],
        buyer_name=me["fullname"],
        history=history
    )
# ----------------listchat------------------
@app.route("/inbox")
@login_required
def chat_inbox():
    uid = session["user"]["id"]

    # Subquery A: tin nhắn cuối mỗi room
    # Subquery B: tin nhắn gần nhất mỗi room có chứa listing_id (không null)
    sql = """
      SELECT
        lm.room,
        m.id            AS last_id,
        m.text          AS last_text,
        m.sender_id     AS last_sender_id,
        m.created_at    AS last_time,

        pm.listing_id   AS product_id,
        pm.listing_title AS product_title,
        pm.listing_cover AS product_cover

      FROM
        ( SELECT room, MAX(id) AS last_id
          FROM chat_messages
          WHERE room LIKE %s OR room LIKE %s
          GROUP BY room
        ) AS lm
      JOIN chat_messages m
        ON m.id = lm.last_id

      LEFT JOIN
        ( SELECT room, MAX(id) AS last_prod_id
          FROM chat_messages
          WHERE (room LIKE %s OR room LIKE %s) AND listing_id IS NOT NULL
          GROUP BY room
        ) AS lp
        ON lp.room = lm.room

      LEFT JOIN chat_messages pm
        ON pm.id = lp.last_prod_id

      ORDER BY m.created_at DESC
    """
    pattern_a = f"chat:{uid}-%"
    pattern_b = f"chat:%-{uid}"

    with closing(get_conn()) as conn, closing(conn.cursor(dictionary=True)) as cur:
        cur.execute(sql, (pattern_a, pattern_b, pattern_a, pattern_b))
        rows = cur.fetchall() or []

    # Tìm other_id
    threads, other_ids = [], set()
    for r in rows:
        try:
            _, pair = r["room"].split(":", 1)
            a_str, b_str = pair.split("-", 1)
            a, b = int(a_str), int(b_str)
            other_id = b if uid == a else a
        except Exception:
            continue
        r["other_id"] = other_id
        other_ids.add(other_id)
        threads.append(r)

    # Lấy info user phía bên kia
    users_map = {}
    if other_ids:
        q = "SELECT id, fullname, email FROM users WHERE id IN (" + ",".join(["%s"]*len(other_ids)) + ")"
        with closing(get_conn()) as conn, closing(conn.cursor(dictionary=True)) as cur:
            cur.execute(q, tuple(other_ids))
            for u in cur.fetchall() or []:
                users_map[u["id"]] = u

    # Ghép dữ liệu render (kèm product chip nếu có)
    def to_image_url(name):
        if not name: return url_for("static", filename="img/placeholder.png")
        import os
        return url_for("uploaded_file", filename=os.path.basename(name))

    result = []
    for r in threads:
        u = users_map.get(r["other_id"])
        item = {
            "room": r["room"],
            "other_id": r["other_id"],
            "other_name": (u and u["fullname"]) or f"User #{r['other_id']}",
            "other_email": (u and u["email"]) or "",
            "last_text": r["last_text"],
            "last_time": r["last_time"],
        }
        # Nếu phòng từng có nhắc tới sản phẩm => build chip
        if r.get("product_id"):
            item["product"] = {
                "id": r["product_id"],
                "title": r.get("product_title") or f"Sản phẩm #{r['product_id']}",
                "image_url": to_image_url(r.get("product_cover")),
                "link": url_for("listing_detail", id=r["product_id"]) if "listing_detail" in app.view_functions else f"/listing/{r['product_id']}",
            }
        else:
            item["product"] = None

        result.append(item)

    return render_template("chat_inbox.html", threads=result)

# ------------chat1-1---------------
@app.route("/listing/<int:id>/chat-oto")
@login_required
def listing_chat_oto(id):
    with closing(get_conn()) as conn, closing(conn.cursor(dictionary=True)) as cur:
        cur.execute("""
            SELECT l.id, l.title, l.user_id AS seller_id, u.fullname AS seller_name
            FROM listings l
            JOIN users u ON u.id = l.user_id
            WHERE l.id=%s AND l.status<>'hidden'
        """, (id,))
        item = cur.fetchone()

    if not item:
        flash("Tin không tồn tại hoặc đã ẩn.", "warning")
        return redirect(url_for("home"))

    buyer_id = session["user"]["id"]
    seller_id = item["seller_id"]

    if buyer_id == seller_id:
        flash("Đây là tin của bạn, không thể chat với chính mình.", "info")
        return redirect(url_for("listing_detail", id=id))

    room = f"chat:{min(buyer_id, seller_id)}-{max(buyer_id, seller_id)}"

    # ✅ Lấy 50 tin nhắn gần nhất từ DB
    history = load_messages(room, 50)

    return render_template("chatOtO.html",
                           item=item,
                           room=room,
                           seller_name=item["seller_name"],
                           buyer_name=session["user"]["fullname"],
                           history=history)

def save_message(room, sender_id, text):
    """Lưu 1 tin nhắn vào DB."""
    sql = "INSERT INTO chat_messages(room, sender_id, text) VALUES (%s, %s, %s)"
    with closing(get_conn()) as conn, closing(conn.cursor()) as cur:
        cur.execute(sql, (room, sender_id, text))
        conn.commit()


def load_messages(room, limit=50):
    """Lấy lịch sử chat theo room, giới hạn số lượng."""
    sql = """
      SELECT m.id, m.room, m.sender_id, u.fullname AS sender_name, m.text, m.created_at
      FROM chat_messages m
      LEFT JOIN users u ON u.id = m.sender_id
      WHERE m.room = %s
      ORDER BY m.id DESC
      LIMIT %s
    """
    with closing(get_conn()) as conn, closing(conn.cursor(dictionary=True)) as cur:
        cur.execute(sql, (room, limit))
        rows = cur.fetchall() or []
    return list(reversed(rows))  # đảo lại để cũ trước, mới sau




@app.route("/listing/<int:id>")
def listing_detail(id):
    with closing(get_conn()) as conn, closing(conn.cursor(dictionary=True)) as cur:
        cur.execute("""
            SELECT l.id, l.title, l.description, l.price, l.category, l.condition_level, l.location,
                   l.cover_image, l.status, l.created_at,
                   u.fullname AS seller_name, u.email AS seller_email
            FROM listings l
            JOIN users u ON u.id = l.user_id
            WHERE l.id = %s AND l.status <> 'hidden'
        """, (id,))
        item = cur.fetchone()
    if not item:
        flash("Tin đăng không tồn tại hoặc đã ẩn.", "warning")
        return redirect(url_for("home"))

    has_buy_route = ("buy_listing" in app.view_functions)  # <— thêm dòng này
    return render_template("listing_detail.html", item=item, has_buy_route=has_buy_route)

# tìm kiếm 

@app.route("/api/suggest")
def api_suggest():
    term = (request.args.get("q") or request.args.get("term") or "").strip()
    items = []
    if not term:
        return jsonify({"query": term, "items": items})

    # ----- Gợi ý theo danh mục (value = name) -----
    try:
        cat_list, _ = load_category_maps(value="name")  # [(name, name)]
    except Exception:
        cat_list = []
    t = term.lower()
    for val, label in cat_list:
        if t in val.lower() or t in label.lower():
            items.append({
                "type": "category",
                "label": label,
                "url": url_for("search", category=val, q=term)
            })
            if len(items) >= 4:
                break

    # ----- Gợi ý tiêu đề/mô tả/danh mục: KHÔNG phân biệt hoa-thường & dấu -----
    COLL = "utf8mb4_0900_ai_ci"  # dùng MySQL 8; nếu không có, thay bằng utf8mb4_unicode_ci
    with closing(get_conn()) as conn:
        try:
            conn.set_charset_collation('utf8mb4', 'utf8mb4_unicode_ci')
        except Exception:
            pass
        with closing(conn.cursor(dictionary=True)) as cur:
            like = f"%{term}%"  # không cần .lower() vì đã COLLATE
            cur.execute(f"""
                SELECT id, title
                FROM listings
                WHERE status='active' AND (
                    title    COLLATE {COLL} LIKE %s OR
                    description COLLATE {COLL} LIKE %s OR
                    category COLLATE {COLL} LIKE %s
                )
                ORDER BY id DESC
                LIMIT 8
            """, (like, like, like))
            rows = cur.fetchall() or []

    for r in rows:
        items.append({
            "type": "listing",
            "id": r["id"],
            "label": r["title"],
            "url": url_for("listing_detail", id=r["id"])
        })

    return jsonify({"query": term, "items": items[:10]})
# --------------tìm kiếm-----------------------


@app.get("/search")
def search():
    q            = (request.args.get("q") or "").strip()
    category     = (request.args.get("category") or "").strip() or None
    cond         = (request.args.get("condition") or "").strip() or None
    min_price_s  = (request.args.get("min_price") or "").replace(",", "").strip()
    max_price_s  = (request.args.get("max_price") or "").replace(",", "").strip()
    sort         = (request.args.get("sort") or "newest").strip()
    page         = request.args.get("page", 1, type=int)
    per_page     = request.args.get("per_page", 24, type=int)
    page         = max(page, 1)
    per_page     = max(min(per_page, 60), 1)

    def parse_decimal(s):
        try: return Decimal(s) if s else None
        except Exception: return None

    min_price = parse_decimal(min_price_s)
    max_price = parse_decimal(max_price_s)

    base_from = """
      FROM listings l
      LEFT JOIN users u ON u.id = l.user_id
      WHERE l.status = 'active'
    """
    where, params = [], []

    if q:
        # Dò title/description/location/category – không cần LOWER/COLLATE vì cột đã utf8mb4_unicode_ci
        where.append("("
                     "l.title LIKE %s OR "
                     "l.description LIKE %s OR "
                     "l.location LIKE %s OR "
                     "l.category LIKE %s"
                     ")")
        like = f"%{q}%"
        params += [like, like, like, like]

    if category:
        where.append("l.category = %s")
        params.append(category)

    if cond:
        where.append("l.condition_level = %s"); params.append(cond)
    if min_price is not None:
        where.append("l.price >= %s"); params.append(min_price)
    if max_price is not None:
        where.append("l.price <= %s"); params.append(max_price)

    where_sql = (" AND ".join(where)) if where else "1=1"

    ORDER_BY = {
        "newest":     "l.created_at DESC",
        "oldest":     "l.created_at ASC",
        "price_asc":  "l.price ASC",
        "price_desc": "l.price DESC",
    }
    order_by = ORDER_BY.get(sort, ORDER_BY["newest"])

    count_sql = f"SELECT COUNT(*) AS total {base_from} AND {where_sql}"
    offset = (page - 1) * per_page
    data_sql = f"""
      SELECT
        l.id, l.title, l.price, l.status, l.created_at,
        l.cover_image, l.category, l.condition_level, l.location,
        u.fullname AS uploader_name
      {base_from} AND {where_sql}
      ORDER BY {order_by}
      LIMIT %s OFFSET %s
    """
    suggest_sql = f"""
      SELECT l.id, l.title
      {base_from} AND {where_sql}
      ORDER BY l.created_at DESC
      LIMIT 8
    """

    with closing(get_conn()) as conn:
        try:
            conn.set_charset_collation('utf8mb4', 'utf8mb4_unicode_ci')
        except Exception:
            pass
        with closing(conn.cursor(dictionary=True)) as cur:
            cur.execute(count_sql, params)
            total = cur.fetchone()["total"] if cur.rowcount is not None else 0
            cur.execute(data_sql, params + [per_page, offset])
            rows = cur.fetchall()
            cur.execute(suggest_sql, params)
            quick_suggestions = cur.fetchall()

    try:
        _, cat_map = load_category_maps(value="name")
    except Exception:
        try: cat_map = dict(load_categories_for_form())
        except Exception: cat_map = {}

    def to_image_url(name):
        if not name: return url_for("static", filename="img/placeholder.png")
        import os
        return url_for("uploaded_file", filename=os.path.basename(name))

    def fmt_price(v):
        try: return f"{Decimal(v):,.0f}₫"
        except Exception: return str(v) if v is not None else ""

    results = []
    for r in rows:
        cat_label  = cat_map.get(r["category"]) or r["category"] or "Khác"
        cond_label = dict(CONDITIONS).get(r["condition_level"], r["condition_level"]) or ""
        caption    = f"{cat_label}" + (f" · {cond_label}" if cond_label else "")
        results.append({
            "url": url_for("listing_detail", id=r["id"]) if "listing_detail" in app.view_functions else f"/listing/{r['id']}",
            "image_url": to_image_url(r.get("cover_image")),
            "name": r["title"] or f"Mục #{r['id']}",
            "intro": " · ".join([s for s in [(r.get("location") or "").strip(), fmt_price(r.get("price"))] if s]),
            "caption": caption,
            "uploader_name": r.get("uploader_name") or "Ẩn danh",
        })

    total_pages = (total + per_page - 1) // per_page if per_page else 1
    total_pages = max(total_pages, 1)
    page = min(page, total_pages)

    def build_url(page_number: int):
        args = request.args.to_dict(flat=True)
        args["page"] = page_number
        args["per_page"] = per_page
        return url_for("search", **args)

    window = 7; half = window // 2
    start = max(page - half, 1); end = min(start + window - 1, total_pages)
    start = max(min(start, max(1, end - window + 1)), 1)
    pages = [{"number": i, "url": build_url(i), "active": (i == page)} for i in range(start, end + 1)]
    pagination = {
        "prev_url": build_url(page - 1) if page > 1 else None,
        "next_url": build_url(page + 1) if page < total_pages else None,
        "pages": pages,
    }

    return render_template(
        "search.html",
        q=q,
        results=results,
        pagination=pagination,
        quick_suggestions=quick_suggestions,
        CATEGORIES=cat_map,
        CONDITIONS=CONDITIONS,
        category=category,
        condition=cond,
        min_price=min_price_s,
        max_price=max_price_s,
        sort=sort,
    )
    
    
    
    # Chatbot API
# =========================================================
@app.route("/chatbot", methods=["POST"])
def chatbot():
    try:
        data = request.get_json(force=True)
        user_message = data.get("message", "")
        if not user_message:
            return jsonify({"reply": "⚠️ Bạn chưa nhập tin nhắn."})

        response = client.chat.completions.create(
            model="gpt-4o-mini",
            messages=[
                {"role": "system", "content": "Bạn là chatbot hỗ trợ người dùng."},
                {"role": "user", "content": user_message}
            ]
        )
        reply = response.choices[0].message.content
        return jsonify({"reply": reply})
    except Exception as e:
        return jsonify({"error": str(e)}), 500

@socketio.on("join_room")
def sio_join_room(data):
    room = (data or {}).get("room")
    if not room: return
    join_room(room)

    u = session.get("user")
    emit("system", {"room": room, "message": f"{(u and u['fullname']) or 'Guest'} đã vào phòng."}, room=room)

    listing_id = (data or {}).get("listing_id")
    if listing_id:
        # lấy sản phẩm
        with closing(get_conn()) as conn, closing(conn.cursor(dictionary=True)) as cur:
            cur.execute("SELECT id, title, cover_image FROM listings WHERE id=%s AND status<>'hidden'", (listing_id,))
            item = cur.fetchone()

        if item:
            # phát product_context để cả 2 bên thấy “tin nhắn sản phẩm”
            from os.path import basename
            product = {
                "id": item["id"],
                "title": item["title"],
                "image_url": url_for("uploaded_file", filename=basename(item["cover_image"])) if item.get("cover_image") else url_for("static", filename="img/placeholder.png"),
                "link": url_for("listing_detail", id=item["id"]),
            }
            emit("product_context", {"room": room, "product": product, "ts": int(time.time())}, room=room)

            # ✅ lưu 1 bản ghi context vào DB để /inbox có thể tìm ra sản phẩm gần nhất
            with closing(get_conn()) as conn, closing(conn.cursor()) as cur:
                cur.execute("""
                    INSERT INTO chat_messages (room, sender_id, text, listing_id, listing_title, listing_cover)
                    VALUES (%s, %s, %s, %s, %s, %s)
                """, (
                    room, (u and u.get("id")),
                    "[context] product",
                    item["id"], item["title"], item.get("cover_image")
                ))
                conn.commit()

# ===================== Main =====================
if __name__ == "__main__":
    # Nhớ Start Apache + MySQL trong XAMPP trước khi chạy
    # app.run(debug=True)
    socketio.run(app, host="0.0.0.0", port=5000, debug=True)
