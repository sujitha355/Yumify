import React from "react";
import chesse from '../images/cheeseCake.webp';
import chicken from '../images/chickenBiriyani.webp';
import chocolate from '../images/chocolateBrownie.webp';
import pizza from '../images/cpizza.webp';
import fried from '../images/friedEggsAvacado.webp';
import pan from '../images/pancake.webp';
import ramen from '../images/ramen.webp';
import '../stylesSheets/Slider.css';
import { useNavigate } from 'react-router-dom';
import axios from "axios";
import { toast, ToastContainer } from 'react-toastify'; 
import 'react-toastify/dist/ReactToastify.css'; 
export default function Slider(props) {
     
    const user = props.user;
    const navigate = useNavigate();

    const handleImageClick = (imageId) => {
        if (user) {
            const formData = new FormData();
            formData.append('userEmail', user.email);
            formData.append('imageId', imageId);
            formData.append('name', user.name);
    
            // Simplified URL without special characters
            const phpUrl = 'http://localhost/recipe-app/php/email.php';
            
            axios.post(phpUrl, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then((res) => {
                if (res.data.success) {
                    toast.success(res.data.message, { autoClose: 2000 });
                } else if (res.data.error) {
                    toast.error(res.data.error, { autoClose: 2000 });
                }
            })
            .catch((err) => {
                console.error('Email sending error details:', {
                    message: err.message,
                    response: err.response,
                    config: err.config
                });
                const errorMessage = err.response?.data?.error || 'Failed to send email. Please try again.';
                toast.error(errorMessage, { autoClose: 2000 });
            });
        } else {
            toast.warning('Please log in first!', { autoClose: 2000 });
            navigate('/login');
        }
    };

    return (
        <>
            <div id="Recipe">
                <h1 className="heading">Popular Recipes</h1>
                <p className="heading1">Click me</p>
                <div className="slider">
                    <img src={chesse} alt="Cheese Cake" id="one" onClick={() => handleImageClick('one')}  loading="lazy"/>
                    <img src={chicken} alt="Chicken Biriyani" id="two" onClick={() => handleImageClick('two')}  loading="lazy"/>
                    <img src={chocolate} alt="Chocolate Brownie" id="three" onClick={() => handleImageClick('three')} loading="lazy"/>
                    <img src={pizza} alt="Pizza" id="four" onClick={() => handleImageClick('four')}  loading="lazy"/>
                    <img src={fried} alt="Fried Eggs Avocado" id="five" onClick={() => handleImageClick('five')}  loading="lazy"/>
                    <img src={pan} alt="Pancake" id="six" onClick={() => handleImageClick('six')} loading="lazy"/>
                    <img src={ramen} alt="Ramen" id="seven" onClick={() => handleImageClick('seven')} loading="lazy"/>
                </div>
            </div>
            <ToastContainer /> 
        </>
    );
}
