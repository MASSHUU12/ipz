import React from 'react';
import { Link } from "@inertiajs/react";

const VerifyEmailThankYou: React.FC = () => {
    return (
        <div style={styles.page}>
            <div style={styles.container}>
                <svg
                    style={styles.icon}
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>

                <h1 style={styles.title}>Weryfikacja zakończona!</h1>

                <p style={styles.text}>
                    Twój adres e-mail został potwierdzony. Możesz teraz korzystać z pełnej funkcjonalności aplikacji.
                </p>

                <Link href="/dashboard" style={styles.button}>
                Przejdź do dashboardu
                </Link>

                <p style={styles.footer}>Dziękujemy za zaufanie!</p>
            </div>
        </div>
    );
};

export default VerifyEmailThankYou;

const styles: Record<string, React.CSSProperties> = {
    page: {
        background: 'radial-gradient(circle, #29144D, #0a0a0a)',
        minHeight: '100vh',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        padding: '20px'
    },
    container: {
        backgroundColor: '#121212',
        padding: '40px',
        borderRadius: '12px',
        textAlign: 'center',
        boxShadow: '0 10px 25px rgba(0, 0, 0, 0.3)',
        maxWidth: '500px',
        width: '100%'
    },
    icon: {
        width: '60px',
        height: '60px',
        margin: '0 auto 20px',
        color: '#8a2be2'
    },
    title: {
        fontSize: '2rem',
        color: '#fff',
        marginBottom: '20px'
    },
    text: {
        fontSize: '1rem',
        color: '#ddd',
        marginBottom: '30px'
    },
    button: {
        background: 'linear-gradient(135deg, #8a2be2, #00bfff)',
        border: 'none',
        color: '#fff',
        padding: '10px 20px',
        borderRadius: '25px',
        cursor: 'pointer',
        fontSize: '1rem'
    },
    footer: {
        marginTop: '20px',
        color: '#aaa',
        fontSize: '0.9rem'
    }
};
