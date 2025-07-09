import React, { useState } from "react";
import { useNavigate } from 'react-router-dom';
import indian from '../jsonFiles/indian.json';
import italian from '../jsonFiles/italian.json';
import korean from '../jsonFiles/korean.json';
import '../stylesSheets/recipe.css';
import Parallax from "./Parallax";

export default function Recipe({ user }) { // Accept 'user' as a prop
    const [selectedRecipe, setSelectedRecipe] = useState(null);
    const [searchTerm, setSearchTerm] = useState("");
    const [likes, setLikes] = useState({});
    const navigate = useNavigate();

    const allRecipes = [...indian, ...italian, ...korean];

    const handleSearchChange = (event) => {
        setSearchTerm(event.target.value);
    };

    const handleImageClick = (recipe) => {
        setSelectedRecipe(recipe);
    };

    const handleClosePopup = () => {
        setSelectedRecipe(null);
    };

    const handleLikeClick = (title) => {
        if (!user) {
            // Redirect to login if user is not logged in
            navigate('/login');
            alert("Please log in to like this recipe.");
            return;
        }
        // Toggle like for logged-in users
        setLikes((prevLikes) => ({
            ...prevLikes,
            [title]: {
                liked: !prevLikes[title]?.liked,
                count: prevLikes[title]?.liked ? prevLikes[title].count - 1 : (prevLikes[title]?.count || 0) + 1,
            },
        }));
    };

    const filteredRecipes = searchTerm
        ? allRecipes.filter(recipe =>
              recipe.title.toLowerCase().includes(searchTerm.toLowerCase())
          )
        : allRecipes;

    return (
        <>
            <h1 className="heading">Global Flavors</h1>
            <input
                type="text"
                placeholder="Search for a recipe..."
                value={searchTerm}
                onChange={handleSearchChange}
                className="search-bar"
            />
            
            {filteredRecipes.length === 0 ? (
                <p className="no-recipes-message">No recipes found. Try a different search term.</p>
            ) : (
                <div className="recipe-list">
                    {filteredRecipes.map((element, index) => (
                        <li key={index} className="recipe-item">
                            <div className="image-container" onClick={() => handleImageClick(element)}>
                                <img
                                    src={element.Pic}
                                    alt={element.title}
                                    className="recipe-image"
                                />
                                <p className="image-title">{element.title}</p>
                            </div>
                            <button
                                onClick={() => handleLikeClick(element.title)}
                                className={`like-button ${likes[element.title]?.liked ? 'liked' : ''}`}
                            >
                                <i className="fas fa-heart"></i> {likes[element.title]?.count || 0}
                            </button>
                        </li>
                    ))}
                </div>
            )}

            {selectedRecipe && (
                <div className={`popup ${selectedRecipe ? 'show' : ''}`}>
                    <div className="popup-content">
                        <span className="close" onClick={handleClosePopup}>&times;</span>
                        <h2>{selectedRecipe.title}</h2>
                        <p><strong>Ingredients:</strong> {selectedRecipe.ingredient.join(", ")}</p>
                        <p><strong>Preparation:</strong></p>
                        <ul className="preparation-list">
                            {selectedRecipe.preparation.map((step, index) => (
                                <li key={index}>{step}</li>
                            ))}
                        </ul>
                    </div>
                </div>
            )}
            <Parallax />
        </>
    );
}
