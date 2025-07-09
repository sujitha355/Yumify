import React from "react";
import '../stylesSheets/footer.css';
export default function Footer(){
    function scrollToSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.scrollIntoView({ behavior: 'smooth' });
        }
    }
    return(
        <>
            <footer className="footer">
                <div className="footer-container">
                    <div className="footer-section about">
                    <h2>About Us</h2>
                    <p>Sharing delicious recipes from around the world! Follow us for more tasty inspiration.</p>
                    </div>
                    
                    <div className="footer-section links">
                    <h2>Quick Links</h2>
                    <ul>
                        <li onClick={()=>scrollToSection('Home')}>Home</li>
                        <li onClick={()=>scrollToSection('Recipe')}>Recipes</li>
                        <li onClick={()=>scrollToSection('About')}>About Us</li>
                        <li onClick={()=>scrollToSection('Contact')}>Contact</li>
                    </ul>
                    </div>
                    
                    <div className="footer-section contact">
                    <h2>Contact Us</h2>
                    <p>Email: sujithap144@gmail.com</p>
                    <p>Phone: +917672057636</p>
                    <div className="socials">
                        <a href="https://www.linkedin.com/in/sujitha-perabathula-667b4b312" target="_blank" rel="noopener noreferrer"><i className="fab fa-linkedin"></i></a>
                        <a href="https://github.com/sujitha355" target="_blank" rel="noopener noreferrer"><i className="fab fa-github"></i></a>
                        <a href="https://www.instagram.com/sujitha__355?igsh=NjNrNGFqc2Njdjds" target="_blank" rel="noopener noreferrer"><i className="fab fa-instagram"></i></a>
                    </div>
                    </div>
                </div>
                <div className="footer-bottom">
                    &copy; 2025 Yumify | All rights reserved
                </div>
            </footer>

        </>
    )
}