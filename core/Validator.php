<?php
/**
 * ANTRIAN SEHAT - Validator Helper
 * 
 * Kelas untuk validasi input data.
 * Mendukung berbagai aturan validasi termasuk: required, email, 
 * min, max, numeric, unique, date, phone, dan nik.
 */

class Validator
{
    /**
     * Pesan error validasi
     * @var array
     */
    protected array $errors = [];

    /**
     * Koneksi database untuk validasi unique
     * @var PDO|null
     */
    protected ?PDO $db = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        try {
            $this->db = Database::getInstance()->getConnection();
        } catch (\Exception $e) {
            // Database mungkin belum tersedia, lanjutkan tanpa db
            $this->db = null;
        }
    }

    /**
     * Validasi data berdasarkan aturan yang diberikan
     * 
     * Format rules:
     *   'field' => 'required|email|min:5|max:100|unique:users,email,1'
     * 
     * Aturan yang didukung:
     *   - required          : field wajib diisi
     *   - email             : harus format email valid
     *   - min:n             : panjang minimal n karakter
     *   - max:n             : panjang maksimal n karakter
     *   - numeric           : harus berupa angka
     *   - unique:table,col  : harus unik di tabel (opsional: ,excludeId)
     *   - date              : harus format tanggal valid
     *   - phone             : harus format nomor telepon valid
     *   - nik               : harus 16 digit angka (NIK Indonesia)
     *   - confirmed:field   : harus sama dengan field lain
     *   - in:val1,val2,...  : harus salah satu dari nilai yang diberikan
     *   - alpha             : hanya huruf
     *   - alpha_num         : hanya huruf dan angka
     *   - alpha_spaces      : hanya huruf dan spasi
     *   - integer           : harus bilangan bulat
     *   - min_value:n       : nilai minimal (untuk angka)
     *   - max_value:n       : nilai maksimal (untuk angka)
     * 
     * @param array $data Data yang akan divalidasi
     * @param array $rules Aturan validasi ['field' => 'rule1|rule2', ...]
     * @param array $customMessages Pesan kustom opsional ['field.rule' => 'pesan']
     * @return array Array error, kosong jika valid
     */
    public function validate(array $data, array $rules, array $customMessages = []): array
    {
        $this->errors = [];

        foreach ($rules as $field => $ruleString) {
            $fieldLabel = $this->getFieldLabel($field);
            $rulesArray = explode('|', $ruleString);
            $value = $data[$field] ?? null;

            foreach ($rulesArray as $rule) {
                $ruleName = $rule;
                $ruleParam = null;

                // Cek apakah rule memiliki parameter (e.g., min:5)
                if (strpos($rule, ':') !== false) {
                    list($ruleName, $ruleParam) = explode(':', $rule, 2);
                }

                $errorKey = $field . '.' . $ruleName;

                switch ($ruleName) {
                    case 'required':
                        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                            $this->errors[$field] = $customMessages[$errorKey] ?? "{$fieldLabel} wajib diisi.";
                        }
                        break;

                    case 'email':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $this->errors[$field] = $customMessages[$errorKey] ?? "{$fieldLabel} harus berupa alamat email yang valid.";
                        }
                        break;

                    case 'min':
                        if (!empty($value) && mb_strlen($value) < (int) $ruleParam) {
                            $this->errors[$field] = $customMessages[$errorKey] ?? "{$fieldLabel} minimal {$ruleParam} karakter.";
                        }
                        break;

                    case 'max':
                        if (!empty($value) && mb_strlen($value) > (int) $ruleParam) {
                            $this->errors[$field] = $customMessages[$errorKey] ?? "{$fieldLabel} maksimal {$ruleParam} karakter.";
                        }
                        break;

                    case 'numeric':
                        if (!empty($value) && !is_numeric($value)) {
                            $this->errors[$field] = $customMessages[$errorKey] ?? "{$fieldLabel} harus berupa angka.";
                        }
                        break;

                    case 'integer':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                            $this->errors[$field] = $customMessages[$errorKey] ?? "{$fieldLabel} harus berupa bilangan bulat.";
                        }
                        break;

                    case 'unique':
                        if (!empty($value) && $this->db !== null) {
                            $parts = explode(',', $ruleParam);
                            $table = $parts[0] ?? '';
                            $column = $parts[1] ?? $field;
                            $excludeId = $parts[2] ?? null;

                            if ($this->isNotUnique($table, $column, $value, $excludeId)) {
                                $this->errors[$field] = $customMessages[$errorKey] ?? "{$fieldLabel} sudah terdaftar dalam sistem.";
                            }
                        }
                        break;

                    case 'date':
                        if (!empty($value)) {
                            $date = \DateTime::createFromFormat('Y-m-d', $value);
                            if (!$date || $date->format('Y-m-d') !== $value) {
                                $this->errors[$field] = $customMessages[$errorKey] ?? "{$fieldLabel} harus berupa tanggal yang valid (format: YYYY-MM-DD).";
                            }
                        }
                        break;

                    case 'phone':
                        if (!empty($value)) {
                            // Format nomor telepon Indonesia: 08xxx, +628xxx, 628xxx
                            $cleaned = preg_replace('/[\s\-\(\)]/', '', $value);
                            if (!preg_match('/^(\+62|62|0)[2-9][0-9]{7,12}$/', $cleaned)) {
                                $this->errors[$field] = $customMessages[$errorKey] ?? "{$fieldLabel} harus berupa nomor telepon yang valid.";
                            }
                        }
                        break;

                    case 'nik':
                        if (!empty($value)) {
                            $cleaned = preg_replace('/\s/', '', $value);
                            if (!preg_match('/^[0-9]{16}$/', $cleaned)) {
                                $this->errors[$field] = $customMessages[$errorKey] ?? "{$fieldLabel} harus terdiri dari 16 digit angka.";
                            }
                        }
                        break;

                    case 'confirmed':
                        $confirmField = $ruleParam ?? $field . '_confirmation';
                        $confirmValue = $data[$confirmField] ?? null;
                        if ($value !== $confirmValue) {
                            $this->errors[$field] = $customMessages[$errorKey] ?? "Konfirmasi {$fieldLabel} tidak cocok.";
                        }
                        break;

                    case 'in':
                        if (!empty($value)) {
                            $allowedValues = explode(',', $ruleParam);
                            if (!in_array($value, $allowedValues)) {
                                $this->errors[$field] = $customMessages[$errorKey] ?? "{$fieldLabel} harus salah satu dari: " . implode(', ', $allowedValues) . ".";
                            }
                        }
                        break;

                    case 'alpha':
                        if (!empty($value) && !preg_match('/^[a-zA-Z]+$/', $value)) {
                            $this->errors[$field] = $customMessages[$errorKey] ?? "{$fieldLabel} hanya boleh berisi huruf.";
                        }
                        break;

                    case 'alpha_num':
                        if (!empty($value) && !preg_match('/^[a-zA-Z0-9]+$/', $value)) {
                            $this->errors[$field] = $customMessages[$errorKey] ?? "{$fieldLabel} hanya boleh berisi huruf dan angka.";
                        }
                        break;

                    case 'alpha_spaces':
                        if (!empty($value) && !preg_match('/^[a-zA-Z\s]+$/', $value)) {
                            $this->errors[$field] = $customMessages[$errorKey] ?? "{$fieldLabel} hanya boleh berisi huruf dan spasi.";
                        }
                        break;

                    case 'min_value':
                        if (!empty($value) && is_numeric($value) && (float) $value < (float) $ruleParam) {
                            $this->errors[$field] = $customMessages[$errorKey] ?? "{$fieldLabel} minimal bernilai {$ruleParam}.";
                        }
                        break;

                    case 'max_value':
                        if (!empty($value) && is_numeric($value) && (float) $value > (float) $ruleParam) {
                            $this->errors[$field] = $customMessages[$errorKey] ?? "{$fieldLabel} maksimal bernilai {$ruleParam}.";
                        }
                        break;
                }

                // Jika sudah ada error untuk field ini, lanjut ke field berikutnya
                if (isset($this->errors[$field])) {
                    break;
                }
            }
        }

        return $this->errors;
    }

    /**
     * Cek apakah nilai tidak unik di database
     * 
     * @param string $table Nama tabel
     * @param string $column Nama kolom
     * @param mixed $value Nilai yang dicek
     * @param string|null $excludeId ID yang dikecualikan
     * @return bool True jika tidak unik (sudah ada)
     */
    protected function isNotUnique(string $table, string $column, $value, ?string $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$table} WHERE {$column} = :value";
        $params = [':value' => $value];

        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $result = $stmt->fetch();

        return (int) $result->total > 0;
    }

    /**
     * Konversi nama field ke label yang lebih readable
     * 
     * @param string $field
     * @return string
     */
    protected function getFieldLabel(string $field): string
    {
        $labels = [
            'nama_lengkap'      => 'Nama lengkap',
            'email'             => 'Email',
            'username'          => 'Username',
            'password'          => 'Password',
            'password_confirmation' => 'Konfirmasi password',
            'no_telepon'        => 'Nomor telepon',
            'no_hp'             => 'Nomor HP',
            'nik'               => 'NIK',
            'tanggal_lahir'     => 'Tanggal lahir',
            'jenis_kelamin'     => 'Jenis kelamin',
            'alamat'            => 'Alamat',
            'golongan_darah'    => 'Golongan darah',
            'no_bpjs'           => 'Nomor BPJS',
            'no_rm'             => 'Nomor rekam medis',
            'kode_poli'         => 'Kode poli',
            'nama_poli'         => 'Nama poli',
            'nama_dokter'       => 'Nama dokter',
            'spesialisasi'      => 'Spesialisasi',
            'no_str'            => 'Nomor STR',
            'tanggal_kunjungan' => 'Tanggal kunjungan',
            'keluhan'           => 'Keluhan',
            'role'              => 'Role',
        ];

        if (isset($labels[$field])) {
            return $labels[$field];
        }

        // Fallback: convert underscore to space, capitalize
        return ucfirst(str_replace('_', ' ', $field));
    }

    /**
     * Cek apakah validasi memiliki error
     * 
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Ambil semua error
     * 
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Ambil error pertama
     * 
     * @return string|null
     */
    public function getFirstError(): ?string
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }

    // ============================================================
    // Static Helper Methods
    // ============================================================

    /**
     * Sanitasi string dari karakter berbahaya
     * 
     * @param string $string
     * @return string
     */
    public static function sanitize(string $string): string
    {
        $string = trim($string);
        $string = stripslashes($string);
        $string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
        return $string;
    }

    /**
     * Format tanggal ke format Indonesia (Y-m-d -> d F Y)
     * 
     * @param string $date Tanggal format Y-m-d
     * @return string
     */
    public static function formatDate(string $date): string
    {
        if (empty($date) || $date === '0000-00-00') {
            return '-';
        }

        $bulan = [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return '-';
        }

        $hari = (int) date('d', $timestamp);
        $bln = (int) date('m', $timestamp);
        $tahun = date('Y', $timestamp);

        return $hari . ' ' . ($bulan[$bln] ?? '') . ' ' . $tahun;
    }

    /**
     * Format datetime ke format Indonesia (Y-m-d H:i:s -> d F Y H:i)
     * 
     * @param string $datetime Datetime format Y-m-d H:i:s
     * @return string
     */
    public static function formatDateTime(string $datetime): string
    {
        if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
            return '-';
        }

        $timestamp = strtotime($datetime);
        if ($timestamp === false) {
            return '-';
        }

        return self::formatDate($datetime) . ' ' . date('H:i', $timestamp) . ' WIB';
    }
}
