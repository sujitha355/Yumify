import React, { useState, useRef, useContext, useEffect } from "react";
import { useNavigate } from 'react-router-dom'; 
import { UserContext } from "./UserContext";  // Assuming you have a UserContext set up
import '../stylesSheets/Login.css';

export default function Login() {
    const [isRightPanelActive, setRightPanelActive] = useState(false);
    const [isGapiLoaded, setGapiLoaded] = useState(false);
    const nameref = useRef();
    const emailref = useRef();
    const passwordref = useRef();
    const logemailref = useRef();
    const logpassref = useRef();
    const signinpara = useRef();
    const signuppara = useRef();
    const navigate = useNavigate(); 
    const { user, setUser } = useContext(UserContext);  

    // Initialize users from localStorage
    const [users, setUsers] = useState(() => {
        const savedUsers = localStorage.getItem('users');
        return savedUsers ? JSON.parse(savedUsers) : [];
    });

    // Check if user is already logged in
    useEffect(() => {
        const savedUser = localStorage.getItem('user');
        if (savedUser) {
            const userInfo = JSON.parse(savedUser);
            // Verify the user still exists in the users list
            const userExists = users.find(u => u.email === userInfo.email && u.password === userInfo.password);
            if (userExists) {
                setUser(userInfo);
                navigate('/home');
            } else {
                // If user no longer exists in users list, clear the stored user
                localStorage.removeItem('user');
                setUser(null);
            }
        }
    }, [users]);

    // Update localStorage when users change
    useEffect(() => {
        localStorage.setItem('users', JSON.stringify(users));
    }, [users]);

    const handleSignUpClick = () => {
        setRightPanelActive(true);
    };

    const handleSignInClick = () => {
        setRightPanelActive(false);
    };

    function add(event) {
        event.preventDefault();
        const newUser = {
            name: nameref.current.value,
            email: emailref.current.value,
            password: passwordref.current.value
        };   
        signuppara.current.textContent = "";

        // Check if user already exists
        const existingUser = users.find(user => user.email === newUser.email);
        if (existingUser) {
            signuppara.current.textContent = 'User already exists.';
            signuppara.current.style.color = 'red';
            return;
        }

        // Add user to the list and localStorage
        setUsers(prevUsers => [...prevUsers, newUser]);
        localStorage.setItem('users', JSON.stringify([...users, newUser]));
        signuppara.current.textContent = 'User registered successfully!';
        signuppara.current.style.color = 'green';
        
        // Clear input fields
        nameref.current.value = '';
        emailref.current.value = '';
        passwordref.current.value = '';

        // Automatically log in the new user with complete user info
        const userInfo = {
            name: newUser.name,
            email: newUser.email,
            password: newUser.password // Include password for relogin
        };
        setUser(userInfo);
        localStorage.setItem('user', JSON.stringify(userInfo));
        navigate('/home');
    }
    
    function Log(event) {
        event.preventDefault();
        const email = logemailref.current.value;
        const pass = logpassref.current.value;
        const foundUser = users.find(user => user.email === email && user.password === pass);
        signinpara.current.textContent = "";
        
        if (foundUser) {
            // Store complete user info in both context and localStorage
            const userInfo = {
                name: foundUser.name,
                email: foundUser.email,
                password: foundUser.password // Include password for relogin
            };
            setUser(userInfo);
            localStorage.setItem('user', JSON.stringify(userInfo));
            navigate('/home');  
        } else {
            signinpara.current.textContent = 'Email or Password is incorrect';
            signinpara.current.style.color = 'red';
        }
    }

    return (
        <div className="Parent">
            <div className={`container ${isRightPanelActive ? "right-panel-active" : ""}`} id="main">
                <div className="sign-up">
                    <form action="#">
                        <h1>Create Account</h1>
                        <span className="social-container">
                            <a href="#">
                                <i className="fa-brands fa-google-plus-g"></i> {/* Google Icon */}
                            </a>
                            <a href="#"><i className="fa-brands fa-facebook"></i></a>
                            <a href="#"><i className="fa-brands fa-instagram"></i></a>
                        </span>

                        <input type="text" ref={nameref} placeholder="Name" required />
                        <input type="email" ref={emailref} placeholder="Email" required />
                        <input type="password" ref={passwordref} placeholder="Password" required />
                        <p ref={signuppara}></p>
                        <button type="submit" onClick={add} className="login-btn">Sign Up</button>
                    </form>
                </div>

                <div className="sign-in">
                    <form action="#">
                        <h1>Sign In</h1>
                        <span className="social-container">
                            <a href="#">
                                <i className="fa-brands fa-google-plus-g"></i> {/* Google Icon */}
                            </a>
                            <a href="#"><i className="fa-brands fa-facebook"></i></a>
                            <a href="#"><i className="fa-brands fa-instagram"></i></a>
                        </span>

                        <input type="email" ref={logemailref} placeholder="Email" required />
                        <input type="password" ref={logpassref} placeholder="Password" required />
                        <p ref={signinpara}></p>
                        <button type="submit" onClick={Log} className="login-btn">Sign In</button>
                    </form>
                </div>

                <div className="overlay-container">
                    <div className="overlay">
                        <div className="overlay-left">
                            <h1>Welcome Back!</h1>  
                            <p>To keep connected with us, please log in with your personal info</p>
                            <button onClick={handleSignInClick} className="login-btn" style={{border: '3px solid #ccc'}}>Sign In</button>
                        </div>
                        <div className="overlay-right">
                            <h1>Hello, Friend!</h1>
                            <p>Enter your personal details and start your journey with us</p>
                            <button onClick={handleSignUpClick} className="login-btn" style={{border: '3px solid #ccc'}}>Sign Up</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
